 <?php

include '../vendor/ZF2/library/Zend/Oracle/Oracle.php';

$url  = 'https://hpn.horizonstelecom.com/jsonrpc';
$url  = 'http://10.100.0.43:2000/jsonrpc';

$proxy = '10.100.0.4:3128';
$login 	  = 'ossuser';
$password = 'ossuser';

$oracle = new Oracle($url, $proxy, $login, $password);

$auth = $oracle->callAPI( 'auth', Array('username' => $login, 'password'  => $password) , null );
$auth = $auth->result->auth;


$incidents = $oracle->callAPI('rightNow.base_incident', null, $auth);
// var_dump($incidents);

delete();

foreach ($incidents->result->data as $incident) {


	$id = $incident->ID;
	$tn = $incident->ReferenceNumber;
	$subject = $incident->Subject;
	
	$opentime = strtotime($incident->CreatedTime);
    $opentime = date('Y-m-d H:i:s', $opentime);
	
	$closetime = strtotime($incident->ClosedTime);
    $closetime = date('Y-m-d H:i:s', $closetime);

	
	($closetime == '') ? $closetime = $opentime : $closetime = $closetime;

	$severity = $incident->Severity;
	$causa = $incident->CausaName;
	$solucao = $incident->SolucaoName;
	$type = $incident->type2;

	$state = $incident->state;
	($state == 'Resolvido') ? $state = 'closed successful' : $state = 'open';

	$openbyName = $incident->openbyName;
	$closebyName = $incident->closebyName;
	$description = $incident->description;
	$organization = $incident->Organization;

	//Dados do cliente
	$customerData = customerData($oracle, $auth, $organization);

	$cnpj = $customerData['cnpj'];
	$customerName = $customerData['name'];

	//Dados do produto
	$assetData = assetData($oracle, $auth, $incident->Asset);

	$serialNumber = $assetData['SerialNumber'];
	$State = $assetData['State'];
	$InstalledDate = $assetData['InstalledDate'];
	$Product = $assetData['Name'];
	$LookupName = $assetData['LookupName'];

	$timeTotal = timeTotal($opentime, $closetime);
	$tmaTotal = tmaTotal($timeTotal);

	// var_dump($customerData);

	//Historico do incidente
	$historyIncident = historyIncident($oracle, $auth, $id);	

	$sql = "INSERT INTO crm_otrs_ticket_summary (
										tn, 
										title, 
										customer, 
										service, 
										opentime, 
										closetime, 
										priority, 
										type, 
										status, 
										detail, 
										product, 
										description, 
										atendimento, 
										tratamento_tecnico, 
										binario,
										other,
										timetotal,
										openby,
										closeby,
										tmatotal,
										id,
										causa,
										time_causa_note,
										solucao,
										customer_name,
										tma_total_atendimento,
										tma_total_atendimento_tecnico
									)values (
										'$tn', 
										'$subject', 
										'$cnpj', 
										'$serialNumber', 
										'$opentime', 
										'$closetime', 
										'$severity', 
										'$type', 
										'$state', 
										'null', 
										'$Product', 
										'$description', 
										0,
										0,
										0,
										0,
										$timeTotal,
										'$openbyName',
										'$closebyName',
										'$tmaTotal',
										'$id',
										'$causa',
										0,
										'$solucao',
										'$customerName',
										'null',
										'null'
									)";	

	
	 echo $sql . "\n";
	insert($sql);
}
	



//=============================================================================
//MÉTODOS

function customerData($oracle, $auth, $organization)
{	
	$param = array( 'filter' => array( array('attribute' => 'id', 'value' => $organization) ) );
	$organization = $oracle->callAPI('rightNow.get_organization', $param, $auth);
	$arr = array();

	foreach ($organization->result->organization as $o ) {
		
		$arr = array( 
				'cnpj' => $o->cnpj,
				'name' => $o->Name 
			);

	}

	return $arr;

}

function historyIncident($oracle, $auth, $incident)
{	
	$param = array( 'id' => $incident );
	$incident = $oracle->callAPI('rightNow.get_incident_history', $param, $auth);
	$arr = array();

	// var_dump($incident->result->incident);
	foreach ($incident->result->incident as $o ) {
		// var_dump($o);	
	// 	$arr = array( 
	// 			'cnpj' => $o->cnpj,
	// 			'name' => $o->Name 
	// 		);

	}

	return $arr;

}

function assetData($oracle, $auth, $asset)
{	
	$param = array( 'filter' => array( array('attribute' => 'id', 'value' => $asset) ) );
	$asset = $oracle->callAPI('rightNow.get_asset', $param, $auth);
	$arr = array();

	foreach ($asset->result->assets as $a ) {
		$arr = array( 
				'SerialNumber' => $a->SerialNumber,
				'State' => $a->State,
				'InstalledDate' => $a->InstalledDate,
				'Product' => $a->Product,
				'LookupName' => $a->LookupName,
				'Name' => $a->Name,
			);

	}

	return $arr;

}


function queueTime( $ticket_id, $create_time, $closeDate )
{
	//seleciona todo o histórico de mudança de fila do ticket
	$sql = "SELECT 
				create_time,
				queue_id
			FROM ticket_history
			WHERE 
				history_type_id = 16 and 
				ticket_id = $ticket_id 
			ORDER BY create_time ASC ";

	$query = pg_query($sql);

	$atendimento = Array();
	$binario = Array();
	$tratamentoTecnico = Array();
	$other = Array();

	$currentTime = strtotime($create_time); //Transforma data de abertura do ticket para o tipo time
	$closeDate = strtotime($closeDate); //Transforma data de fechamento do ticket para o tipo time
	//$queue = 5; //O tempo começa a contar na fila atendimento

	
	$sqlAux = "SELECT 
		create_time,
		queue_id
	FROM ticket_history
	WHERE 
		history_type_id = 1 and 
		ticket_id = $ticket_id 
	ORDER BY create_time ASC ";

	$queryAux = pg_query($sqlAux);
	$arrayAux = pg_fetch_array($queryAux);
	$queue = $arrayAux['queue_id']; //Fila inicial do ticket

	while ( $array = pg_fetch_array($query) ) {
		

		$moveTime = strtotime( $array['create_time'] ); //data em o ticket foi movido de fila

		//Se fila = atendimento
		if( $queue == 5){
			$time = $moveTime - $currentTime; //data em que o ticket foi movido menos a data anterior
			array_push($atendimento, $time ); //acrescenta valor no array correspondente
		}
		//Se fila = tratamento tecnico
		else if( $queue == 6){
			$time = $moveTime - $currentTime; //data em que o ticket foi movido menos a data anterior
			array_push($tratamentoTecnico, $time ); //acrescenta valor no array correspondente
		}
		//Se fila = binario 
		else if ( $queue == 39 ){
			$time = $moveTime - $currentTime; //data em que o ticket foi movido menos a data anterior
			array_push($binario, $time ); //acrescenta valor no array correspondente
		}
		else{
			$time = $moveTime - $currentTime; //data em que o ticket foi movido menos a data anterior
			array_push($other, $time );	//acrescenta valor no array correspondente
		}

		$currentTime = $moveTime; //altera a data anterior 
		$queue = $array['queue_id']; //fila em o ticket foi movido
	}

	$extraTime = $closeDate - $currentTime;	//tempo entre a ultima mudança de fila e fechamento do ticket
	array_push($atendimento, $extraTime ); //acrescenta no array $atendimento, pois é a área/fila que fecha o chamado
	
	$sumAtendimento = array_sum($atendimento); //soma os valores do array
	$sumBinario = array_sum($binario); //soma os valores do array
	$sumTratamentoTecnico = array_sum($tratamentoTecnico); //soma os valores do array
	$sumOther = array_sum($other); //soma os valores do array

	$times = Array(
			'atendimento' => $sumAtendimento,
			'binario' => $sumBinario,
			'tratamentoTecnico' => $sumTratamentoTecnico,
			'other' => $sumOther
		); //array com os valores por fila processados 

	return $times;
			
}

function timeTotal($openDate, $closeDate)
{	
	$openDate = strtotime($openDate);
	$closeDate = strtotime($closeDate);

	$timeTotal = $closeDate - $openDate;

	return $timeTotal;
}

function tmaTotal($time)
{	
	$timeTotal = $time / 3600;

   	if ($timeTotal <= 0){
		$tmaTotal = "FCR";
	}
    else if ($timeTotal > 0 && $timeTotal <= 4){
    	$tmaTotal = "Até 4 horas";
	}
    else if ($timeTotal > 4 && $timeTotal <= 8){
    	$tmaTotal = "De 4 a 8 horas";
	}
    else if ($timeTotal > 8 && $timeTotal <= 12){
    	$tmaTotal = "De 8 a 12 horas";
	}
    else{
    	$tmaTotal = "Acima de 12 horas";
	}
	return $tmaTotal;
}




function insert($ticket)
{
	include 'PSQL_OSS.php';
	pg_query($ticket);
	
	include 'PSQL_OTRS_CRM.php';
}

function delete()
{
	include 'PSQL_OSS.php';
	$sql = "DELETE FROM crm_otrs_ticket_summary WHERE opentime >= '2015-04-27'";
	pg_query($sql);
}
	


	
?>
