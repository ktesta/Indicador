<?php 

	//conexão com o servidor
	$conect = pg_connect("host=10.100.0.37 port=5432 dbname=otrs user=otrsro password=otrsro");
 
	// Caso a conexão seja reprovada, exibe na tela uma mensagem de erro
	if (!$conect) die ("<h1>Falha na conecao com o Banco de Dados!</h1>");
 

		
?>