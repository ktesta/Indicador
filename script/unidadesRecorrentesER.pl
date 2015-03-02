#!/usr/bin/perl

use warnings;
use DBI;
use strict;
use Data::Dumper;
use MongoDB;
use POSIX qw(strftime);
use MIME::Lite;
use Encode qw/encode decode/;
use utf8;


my $html = "<style> 
					table{  padding: 8px;
							line-height: 20px;
							text-align: left;
							vertical-align: top;
							border-top: 1px solid #dddddd; 
							font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
							font-size: 14px;
					}
					th{ 	font-weight: bold;
					} 
			</style>";
	$html .= "<table >";
	$html .= "<tr style=''>";
	$html .= "<th>Unidades</th>";
	$html .= "<th>Tickets</th>";
	$html .= "<th>Data de Abertura</th>";
	$html .= "<th>Tempo total</th>";
	$html .= "<th>Titulo</th>";
	$html .= "<th>Afeta Servico</th>";
	$html .= "<th>Causa</th>";
	$html .= "<th>Solucao</th>";
	$html .= "</tr>";

my $dbh = DBI->connect('DBI:Pg:dbname=oss;host=10.100.0.37;port=5432', 'oss', 'httoss') 
		or die "Connect error  $DBI::errstr\n";

my $bodyEmail = &tickets;

if ( !$bodyEmail eq '' ){

	if( &check($bodyEmail) ) {
		$html .= $bodyEmail;
		$html .= "</table>";		
		&sendEmail( $html );
	}	

} else {
	#Nada para enviar
}




sub tickets{

	my $date = strftime "%Y-%m-%d", localtime(time - (30*86400)) ;	
	my $body = '' ;

	my $sth = $dbh->prepare( "SELECT 
								tn, totaltime, service_affected,title, opentime, closetime, sintoma, causa, solucao, customers_affected 
							FROM otrs_ticket_summary 
							WHERE 
								  closetime >= '$date' AND 
								  causa NOT LIKE '%Rede do Cliente%'
							ORDER BY opentime ASC");

	$sth->execute();

	my %ticket;
	my %recTickets;

	while ( my @row = $sth->fetchrow_array ){
		my ( $tn, $totaltime, $service_affected, $title, $opentime, $closetime, $sintoma, $causa, $solucao, $customers_affected ) = @row;
		my $rows = $sth->rows;
		my $days = int($totaltime/(24*60*60));
		my $hours = ($totaltime/(60*60))%24;
		my $mins = ($totaltime/60)%60;

		my @customers = $customers_affected =~ /(\w{3,4}-(?:C-)?\d+[-\w\d]*)/g;
		foreach my $customer (@customers) {

			$recTickets{$customer}{$tn} = {
				tn => $tn, 
				opentime => $opentime,
				customer => $customer,
				time => $days.'d '. $hours . 'h ' . $mins . 'm',
				title => $title,
				service_affected => $service_affected, 
				causa => $causa,
				solucao => $solucao,

			};
		}
	}	
	
	my $bodyEmail = '';
	foreach my $customer ( keys %recTickets ) {
		my $count = 0; 
		my $body = '';
		foreach my $ticket ( keys %{$recTickets{$customer}} ) {	
			$body .= "<tr>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{customer} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{tn} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{opentime} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{time} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{title} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{service_affected} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{causa} . "</td>";
			$body .= "<td>" . $recTickets{$customer}{$ticket}{solucao} . "</td>";
			$body .= "</tr>";

			$count++;

		}
		if ( $count > 1 ){
			$bodyEmail .= $body;
		}

	}

	return $bodyEmail;

}

sub check {

	my @data = @_ ;

	my $sth = $dbh->prepare("SELECT count(*) FROM tickets_recorrentes WHERE body = ?");
	$sth->execute(@data);

	my @row = $sth->fetchrow_array ;

	if ( $row[0] eq '0' ) {
		my $date = strftime "%Y/%m/%d", localtime;

		$sth = $dbh->prepare( "INSERT INTO tickets_recorrentes (date, body) values (?, ?) " );
		$sth->execute( $date, @data);

		return 1;
	}

	return 0;
}

sub sendEmail{
 	
 	my @data = @_ ;
	my $to = 'francine.jardim@horizonstelecom.com';
	my $cc = 'kevin.testa@horizonstelecom.com; ';
	my $from = 'sistemas.oss@horizonstelecom.com';
	my $subject = 'Unidades com tickets recorrentes';
	my $date = strftime "%Y/%m/%d", localtime;
	my $title = "";
	my $message = $data[0];

	my $msg = MIME::Lite->new(
	                 From     => $from,
	                 To       => $to,
	                 Cc       => $cc,
	                 Subject  => encode('MIME-Q', $subject),
	                 Data     => encode('utf8', $title).$message,
	                 Encoding => 'quoted-printable',
	                 Type     => 'text/html',
	                 );
	                 
	$msg->attr('content-type.charset' => 'UTF-8');

	if ($msg->send('smtp', '10.100.0.12')) {
		print "Email Sent Successfully\n";	
	} else {
		print "Falha ao enviar email\n";
	}
	
}