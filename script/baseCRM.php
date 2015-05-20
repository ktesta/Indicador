 <?php

main();
	
//Função principal
function main()
{		
	delete(); //deletando todos os registros 

	include 'PSQL_OTRS_CRM.php';

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
				<th>timeTicket</th>
				<th>causa</th>
				<th>solucao</th> 
				<th>customerName</th> 
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
		$tmaTotal = tmaTotal($create_time, $closeDate, null); //função que retorna o tempo total do ticket (por extenso)
		$tmaTotalAtendimento = tmaTotal( null, null, $queueTime['atendimento'] ); //função que retorna o tempo na fila atendimento do ticket (por extenso)
		$tmaTotalTratamentoTecnico = tmaTotal( null, null, $queueTime['tratamentoTecnico'] ); //função que retorna o tempo na fila atendimento do ticket (por extenso)

		$arrCausa = causa($id); //função que retorna a causa do ticket

		(!empty($arrCausa['time']) ? $timeHashtagCausa = $arrCausa['time'] : $timeHashtagCausa = $closeDate);
		$timeTotal2 = tmaTotal($create_time, $timeHashtagCausa, null); //função que retorna o tempo total do ticket por extenso

		$solucao = solucao($id); //função que retorna a solução do ticket
		$customerData = customerData($customer_id); //função que retorna o nome do cliente

		
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
			$list .= "<td>".$tmaTotal."</td>";
			$list .= "<td>".$arrCausa['causa']."</td>";
			$list .= "<td>".$timeTotal2."</td>";
			$list .= "<td>".$arrCausa['time']."</td>";
			$list .= "<td>".$solucao."</td>";
			$list .= "<td>".$customerData."</td>";
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
										'$closeBy',
										'$tmaTotal',
										'$id',
										'".$arrCausa['causa']."',
										'".$timeTotal2."',
										'$solucao',
										'$customerData',
										'$tmaTotalAtendimento',
										'$tmaTotalTratamentoTecnico'
									)";
		//	echo "<br>";
			//echo $ticket;
			insert($ticket); //inserindo na base dos indicadores
	}

	$list .= "</table>";
	//echo $list;
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


function product( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 35";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function description( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 38";

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

function tmaTotal($openDate, $closeDate, $time)
{	
	if(!empty($openDate) && !empty($closeDate) ){

		$openDate = strtotime($openDate);
		$closeDate = strtotime($closeDate);

		$timeTotal = ($closeDate - $openDate) / 3600;
	}
	if( !empty($time) ) {
		$timeTotal = $time / 3600;
	}
	if( empty($time) && empty($openDate) && empty($closeDate) ){
		echo "opendate".$openDate;
		echo "closedate".$closeDate;
		echo 'return bosta ';
		return 'NULL';
	}

	echo "timetotal:".$timeTotal;

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

function causa($ticket_id)
{
	$sql = "SELECT lower (trim ( trim(  trim(both from a_body), '.' ), '#' ) )  as a_body,
					create_time
			FROM article
			WHERE 
				ticket_id = $ticket_id and  
				( a_subject like '%#Causa%' or 
				  a_subject like '%#causa%' )  		
			ORDER BY id DESC ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);
	$causa = trim( $array['a_body'] );
	$time = $array['create_time'];

	$arr = Array( 'causa' => $causa, 'time' => $time );

	return $arr;
}

function solucao($ticket_id)
{
	$sql = "SELECT a_body 
			FROM article
			WHERE 
				ticket_id = $ticket_id and  
				( a_subject like '%#Solução%' or 
				  a_subject like '%#solução%' or
				  a_subject like '%#solucao%' or 
				  a_subject like '%#soluçao%' 
				 )  		
			ORDER BY id DESC ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['a_body'];
}

function customerData($customer_id)
{
	$sql = "SELECT first_name 
			FROM customer_user 
			WHERE 
				customer_id = '$customer_id' ";
	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['first_name'];
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
	$sql = "DELETE FROM crm_otrs_ticket_summary WHERE opentime < '2015-04-27' ";
	pg_query($sql);
}
	


	
?>
