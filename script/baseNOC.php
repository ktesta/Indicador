 <?php

main();
	
//Função principal
function main()
{		
	//delete(); //deletando todos os registros 

	include 'PSQL_OTRS_NOC.php';

	$sql = "SELECT  ticket.id,
					tn, 
					title, 
					customer_id, 
					customer_user_id, 
					ticket.create_time, 
					ticket_priority.name as ticket_priority_id, 
					ticket_type.name as type_id, 
					ticket_state.name as ticket_state_id
				FROM ticket 
				INNER JOIN ticket_type 
					ON( ticket.type_id = ticket_type.id)
				INNER JOIN ticket_priority
					ON( ticket.ticket_priority_id = ticket_priority.id)
				INNER JOIN ticket_state
					ON( ticket.ticket_state_id = ticket_state.id)	
			";

	$query = pg_query($sql); 

	$list = "<table border='1'>
				<th>tn</th> 
				<th>title</th> 
				<th>customer_id</th> 
				<th>customer_user_id</th> 
				<th>create_time</th>
				<th>close_time</th> 
				<th>ticket_priority_id</th> 
				<th>type_id</th> 
				<th>ticket_state_id</th> 
				<th>details</th> 
				<th>product</th> 
				<th>description</th> 
				<th>atendimento</th> 
				<th>tratamentoTecnico</th> 
				<th>binario</th>
				<th>outros</th>
				<th>timeTotal</th> 
				<th>openBy</th> 
				<th>closeBy</th> 
				<th>nome do cliente</th> 
				<th>endereço</th> 
				<th>CEP</th> 
				<th>cidade</th> 
				<th>data de ativação</th> 
				<th>status adm</th> 
				<th>status operacional</th> 
				<th>velocidade</th> 
				<th>causa</th> 
				<th>solucao</th> 

				";
	

	//Selecionando dados dos tickets			
	while ( $array = pg_fetch_array($query) ) {		

		$id = $array['id']; 
		$tn = $array['tn']; 
		$title = $array['title'];
		$customer_id = $array['customer_id'];
		$customer_user_id = $array['customer_user_id'];
		$create_time = $array['create_time'];
		$ticket_priority_id = $array['ticket_priority_id'];
		$type_id = $array['type_id'];
		$ticket_state_id = $array['ticket_state_id'];
		
		$detail = detail($id); //função para selecionar o valor do campo detalhe
		$product = product($id); //função para selecionar o valor do campo produto	
		$description = description($id); //função para selecionar o valor do campo descrição
		$closeDate = closeDate($id, $create_time); //função que retorna a data de fechamento do ticket
		$closeBy = closeBy($id); //função que retorna o login de quem fechou o ticket
		$openBy = openBy($id); //função que retorna o login de quem abriu o ticket	
		$timeTotal = timeTotal($create_time, $closeDate); //função que retorna o tempo total do ticket	
		$queueTime = queueTime($id, $create_time, $closeDate); //função que retorna um array com o tempo por fila do ticket
		$customerData = customerData($customer_id); //função que retorna um array com os dados do cliente
		$customerDataCMDB = customerDataCMDB($customer_user_id); //função que retorna um array com os dados do cliente
		$causa = causa($id); //função que retorna a causa do ticket
		$solucao = solucao($id); //função que retorna a solução do ticket


		
			$list .= "<tr>";
			$list .= "<td>".$tn.".</td>";
			$list .= "<td>".$title."</td>";	
			$list .= "<td>".$customer_id."</td>";	
			$list .= "<td>".$customer_user_id."</td>";
			$list .= "<td>".$create_time."</td>";
			$list .= "<td>".$closeDate."</td>";	
			$list .= "<td>".$ticket_priority_id."</td>";
			$list .= "<td>".$type_id."</td>";
			$list .= "<td>".$ticket_state_id."</td>";
			$list .= "<td>".$detail."</td>";
			$list .= "<td>".$product."</td>";
			$list .= "<td>".$description."</td>";
			$list .= "<td>".$queueTime['atendimento']."</td>";
			$list .= "<td>".$queueTime['tratamentoTecnico']."</td>";
			$list .= "<td>".$queueTime['binario']."</td>";
			$list .= "<td>".$queueTime['other']."</td>";
			$list .= "<td>".$timeTotal."</td>";
			$list .= "<td>".$openBy."</td>";
			$list .= "<td>".$closeBy."</td>";
			$list .= "<td>".$customerDataCMDB['nome']."</td>";
			$list .= "<td>".$customerDataCMDB['end']."</td>";
			$list .= "<td>".$customerData['zip']."</td>";
			$list .= "<td>".$customerData['city']."</td>";
			$list .= "<td>".$customerDataCMDB['datamig']."</td>";
			$list .= "<td>".$customerDataCMDB['status']."</td>";
			$list .= "<td>".$customerDataCMDB['operstatus']."</td>";
			$list .= "<td>".$customerDataCMDB['vel']."</td>";
			$list .= "<td>".$causa."</td>";
			$list .= "<td>".$solucao	."</td>";
			$list .= "</tr>";
		
	
		 $ticket = "INSERT INTO crm_otrs_ticket_summary (
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
										closeby
									)values (
										'$tn', 
										'$title', 
										'$customer_id', 
										'$customer_user_id', 
										'$create_time', 
										'$closeDate', 
										'$ticket_priority_id', 
										'$type_id', 
										'$ticket_state_id', 
										'$detail', 
										'$product', 
										'$description', 
										".$queueTime['atendimento'].",
										".$queueTime['tratamentoTecnico'].", 
										".$queueTime['binario'].",
										".$queueTime['other'].",
										$timeTotal,
										'$openBy',
										'$closeBy'
									)";
			//echo "<br>";

			//insert($ticket); //inserindo na base dos indicadores
	}

	$list .= "</table>";
	echo $list;
}

//=============================================================================
//MÉTODOS

function detail( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 39";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}


function closeDate( $object_id, $openDate )
{
	$sql = "SELECT create_time 
			FROM ticket_history
			WHERE 
				ticket_id = $object_id and 
				history_type_id = 27 and 
				name like '%%open%%closed successful%%' ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);	

	if( !$array['create_time'] ){
		return $openDate;
	}

	return $array['create_time'];
}

function closeBy( $object_id )
{
	$sql = "SELECT users.login as login
			FROM ticket_history
			INNER JOIN users 
				ON (ticket_history.change_by = users.id)
			WHERE 
				ticket_id = $object_id and ( 
					( 
					  history_type_id = 27 and 
					  name like '%%open%%closed successful%%' 
					) or 
 					( 
					  history_type_id = 1 and 
					  name like '%%closed successful%%' 
					) 
 				)	
				";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);	

	return $array['login'];
}

function openBy( $object_id )
{
	$sql = "SELECT users.login as login
			FROM ticket_history
			INNER JOIN users 
				ON (ticket_history.change_by = users.id)
			WHERE 
				ticket_id = $object_id and 
                history_type_id = 1 ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);	

	return $array['login'];
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
	$queue = 5; //O tempo começa a contar na fila atendimento

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

function insert($ticket)
{
	include 'PSQL_OSS.php';
	pg_query($ticket);
	
	include 'PSQL_OTRS_CRM.php';
}

function delete()
{
	include 'PSQL_OSS.php';
	$sql = "DELETE FROM crm_otrs_ticket_summary";
	pg_query($sql);
}
	
function customerData($customer_id)
{
	$sql = "SELECT  first_name,
					last_name,
					street,
					zip,
					city
			FROM customer_user 
			WHERE 
				customer_id = '$customer_id' ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	$firstname = $array['first_name'];
	$lastname = $array['last_name'];
	$street = $array['street'];
	$zip = $array['zip'];
	$city = $array['city'];

	$data = Array(
			'firstname' => $firstname,
			'lastname' => $lastname,
			'street' => $street,
			'zip' => $zip,
			'city' => $city
		); //array com os valores os dados do cliente

	return $data;
}

function customerDataCMDB($customer_user_id)
{
	include 'MySQL_OSS.php';
	mysql_query('SET character_set_results=utf8');

	$sql = "SELECT 
				datamig,
				statmig,
				status,
				operstatus,
				nome,
				end,
				vel
			FROM customer
			WHERE 
				custid = '$customer_user_id' ";

	$query = mysql_query($sql);
	$array = mysql_fetch_array($query);

	$datamig = $array['datamig'];
	$statmig = $array['statmig'];
	$status = $array['status'];
	$operstatus = $array['operstatus'];
	$nome = $array['nome'];
	$end = $array['end'];
	$vel = $array['vel'];

	$data = Array(
			'datamig' => $datamig,
			'statmig' => $statmig,
			'status' => $status,
			'operstatus' => $operstatus,
			'nome' => $nome,
			'end' => $end,
			'vel' => $vel
		); //array com os valores os dados do cliente

	return $data;

}

	
?>
