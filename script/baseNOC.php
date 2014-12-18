 <?php

main();
	
//Função principal
function main()
{		
	delete(); //deletando todos os registros 

	include 'PSQL_OTRS_NOC.php';

	$sql = "SELECT  ticket.id as id,
					ticket.tn as tn, 
					ticket.title as title, 
					ticket.create_time as opentime,
					ticket_type.name as type,
					users.login as openby,
					ticket_state.name as ts
				FROM ticket 
				INNER JOIN ticket_type
					ON (ticket.type_id = ticket_type.id)
				INNER JOIN users 
					ON (ticket.user_id = users.id) 
				INNER JOIN ticket_state
					ON (ticket.ticket_state_id = ticket_state.id)	
					order by tn desc
			";

	$query = pg_query($sql); 

	$list = "<table border='1'>";
	

	//Selecionando dados dos tickets			
	while ( $array = pg_fetch_array($query) ) {		

		$id = $array['id']; 
		$tn = $array['tn']; 
		$title = $array['title'];
		$opentime = $array['opentime'];
		$openby = $array['openby'];
		$ts = $array['ts'];
		$type = $array['type'];
		$closeData = closeDate( $id, $opentime );
		$causa = causa( $id );
		$solucao = solucao( $id );
		$sintomas = sintomas( $id );
		$servicoAfetado = servicoAfetado( $id );
		$clienteAfetado = clienteAfetado( $id );
		$state = state( $id );
		//$timeState = timeState( $id );
		( !empty($closeData['closetime']) ? $timeTotal = timeTotal($opentime, $closeData['closetime']) : $timeTotal = 0);

			$list .= "<tr>";
			$list .= "<td>".$id."</td>";
			$list .= "<td>".$tn."</td>";
			$list .= "<td>".$type."</td>";
			$list .= "<td>".$title."</td>";	
			$list .= "<td>".$opentime."</td>";	
			$list .= "<td>".$closeData['closetime']."</td>";	
			$list .= "<td>".$openby."</td>";
			$list .= "<td>".$closeData['closeby']."</td>";		
			$list .= "<td>".$ts."</td>";	
			$list .= "<td>".$causa."</td>";	
			$list .= "<td>".$solucao."</td>";	
			$list .= "<td>".$sintomas."</td>";	
			$list .= "<td>".$servicoAfetado."</td>";	
			$list .= "<td>".$clienteAfetado."</td>";
			$list .= "<td>".$timeTotal."</td>";
			$list .= "<td>". ( !empty( $state['HTTSaoJoseCampos'] ) ? $httSJC = array_sum($state['HTTSaoJoseCampos']) : $httSJC = 0 )."</td>";	
			$list .= "<td>". ( !empty( $state['HTTCuritiba'] ) ? $httCTA = array_sum($state['HTTCuritiba']) : $httCTA = 0 )."</td>";	
			$list .= "<td>". ( !empty( $state['HTTMaua'] ) ? $httMUA = array_sum($state['HTTMaua']) : $httMUA = 0 )."</td>";	
			$list .= "<td>". ( !empty( $state['Forjintel'] ) ? $forjintel = array_sum($state['Forjintel']) : $forjintel = 0 )."</td>";	
			$list .= "<td>". ( !empty( $state['HTTOsasco'] ) ? $httOCO = array_sum($state['HTTOsasco']) : $httOCO = 0 )."</td>";	
			$list .= "<td>". ( !empty( $state['Constel'] ) ? $constel = array_sum($state['Constel']) : $constel = 0 )."</td>";	
/*			$list .= "<td>".$queue['SN1']."</td>";	
			$list .= "<td>".$queue['SN2']."</td>";	
			$list .= "<td>".$queue['SN3']."</td>";	
			$list .= "<td>".$queue['OSS']."</td>";	
			$list .= "<td>".$queue['OSP']."</td>";	
			$list .= "<td>".$queue['especialista']."</td>";	
			$list .= "<td>".$queue['infra']."</td>";	
			$list .= "<td>".$queue['wifi']."</td>";	
			$list .= "<td>".$queue['other']."</td>";	*/

			$list .= "</tr>";
		
	
		 echo $ticket = "INSERT INTO otrs_ticket_summary (
		 								id,
										tn,
										ts,
										title,
										type,
										opentime,
										openby,
										closeby,
										closetime,
										totaltime,
										constel,
										forjintel,
										httcuritiba,
										httmaua,
										httosasco,
										httsjc,
										campo,
										client,
										fornecedor,
										other,
										sintoma,
										causa,
										solucao,
										service_affected,
										customers_affected
									)values (
										'$id',
										'$tn', 
										'$ts',
										'$title',
										'$type', 										
										'$opentime', 										
										'$openby', 										
										'".$closeData['closeby']."',
										'".$closeData['closetime']."', 										
										'$timeTotal', 										
										'$constel', 										
										'$forjintel', 										
										'$httCTA', 										
										'$httMUA', 										
										'$httOCO', 										
										'$httSJC', 										
										0, 										
										0, 										
										0, 										
										0, 										
										'$sintomas', 										
										'$causa', 										
										'$solucao',
										'$servicoAfetado',
										'$clienteAfetado'

									)";
			echo "<br>";

			insert($ticket); //inserindo na base dos indicadores
	}

	$list .= "</table>";
	//echo $list;
}

//=============================================================================
//MÉTODOS

function causa( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 71";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function solucao( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 91";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function sintomas( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 51";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function servicoAfetado( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 111";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function clienteAfetado( $object_id )
{
	$sql = "SELECT value_text 
			FROM dynamic_field_value
			WHERE 
				object_id = $object_id and 
				field_id = 131";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);

	return $array['value_text'];
}

function closeDate( $object_id, $openDate )
{
	$sql = "SELECT  ticket_history.create_time,
					users.login as closeby
			FROM ticket_history
			INNER JOIN users 
				ON (ticket_history.change_by = users.id)
			WHERE 
				ticket_id = $object_id and 
				history_type_id = 27 and 
				name like '%closed successful%' ";

	$query = pg_query($sql);
	$array = pg_fetch_array($query);	

	$return = Array( 
				'closetime' => $array['create_time'],
				'closeby' => $array['closeby'] 
			);

	return $return;
}


function state( $ticket_id )
{
	$sql = " SELECT * FROM ticket_history
			WHERE 
				ticket_id = $ticket_id AND 
				( name like '%%FieldName%%AreaAcionada%%Value%%' OR 
				  name = '%%Close' )
			ORDER BY create_time ASC ";

	$query = pg_query($sql);

	$areaPast = NULL;
	$timeState = Array();
	
	while ( $array = pg_fetch_array($query) ){
		
		$area = substr_replace($array['name'], '', 0, 34);

		if( $area != $areaPast){
			if( !empty($date)	 ){
				$time =  strtotime( $array['create_time'] ) - strtotime($date)   ;
				$timeState[$areaPast][] = $time;
			}

			$date = $array['create_time'];
		}	

		$areaPast = $area;
	}
	
	return $timeState;
}

function timeState( $ticket_id )
{
	$sql = " SELECT * FROM ticket_history
			WHERE 
				ticket_id = $ticket_id AND 
				name like '%%Pendente Cliente%%' 
			ORDER BY create_time ASC ";

	$query = pg_query($sql);

	$areaPast = NULL;
	$timeState = Array();
	
	while ( $array = pg_fetch_array($query) ){
		
		$area = $array['state_id'];

		if( !empty($date) ){
			$time =  strtotime( $array['create_time'] ) - strtotime($date)   ;
			$timeState[$area][] = $time;
			$date = NULL;
		}
		else{
			$date = $array['create_time'];
		}

	}
	//var_dump($timeState);
	//( !empty(array_sum($timeState[41]) ) ? $timeState = array_sum($timeState[41]) : 0 );
	
	return $timeState;
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
	
	include 'PSQL_OTRS_NOC.php';
}

function delete()
{
	include 'PSQL_OSS.php';
	$sql = "DELETE FROM otrs_ticket_summary";
	pg_query($sql);
}


	
?>
