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

my $connection = MongoDB::Connection->new(host => '10.100.0.13', port => 27017);
my $database   = $connection->get_database('cmdb');
my $collection = $database->get_collection('CmItem');
my $find       = $collection->query({ 'visible' => 1 });
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
	$html .= "<th>Sintoma</th>";
	$html .= "<th>Causa</th>";
	$html .= "<th>Solucao</th>";
	$html .= "</tr>";

my $dbh = DBI->connect('DBI:Pg:dbname=oss;host=10.100.0.37;port=5432', 'oss', 'httoss') 
		or die "Connect error  $DBI::errstr\n";

while (my $data = $find->next) {

    if( my $class = $data->{class}{name} eq 'Service'){
    	if ( my $state = $data->{attrs}{Status} eq 'Ativo' ){
    		 
    		my $service = $data->{name};    
   	 		
   	 		my $customer = $data->{reldst}[0]{name};
		 	$html .= &tickets($customer);

    	}    	

    }
}
$html .= "</table>";

&sendEmail( $html );


sub tickets{

	my $date = strftime "%Y-%m", localtime;	
	my $date2 = strftime "%Y-%m-%d", localtime;	
	my $body = '' ;

	map { 
		my $unidade = $_ ;

		my $sth = $dbh->prepare( "SELECT 
									tn, totaltime, service_affected,title, opentime, closetime, sintoma, causa, solucao 
								FROM otrs_ticket_summary 
								WHERE customers_affected like ? AND 
									  closetime like '$date%' AND 
									  causa NOT LIKE '%Rede do Cliente%'
								ORDER BY opentime ASC");

		$sth->execute( '%'. $unidade .'%' );

		while ( my @row = $sth->fetchrow_array ){
			my ( $tn, $totaltime, $service_affected, $title, $opentime, $closetime, $sintoma, $causa, $solucao ) = @row;
			my $rows = $sth->rows;
			my $days = int($totaltime/(24*60*60));
			my $hours = ($totaltime/(60*60))%24;
			my $mins = ($totaltime/60)%60;

			if( $rows > 2 ){
				$body .= "<tr>";
				$body .= "<td>" . $unidade . "</td>";
				$body .= "<td>" . $tn . "</td>";
				$body .= "<td>" . $opentime . "</td>";
				$body .= "<td>" . $days . "d ". $hours . "h " . $mins . "m" . "</td>";
				$body .= "<td>" . $title . "</td>";
				$body .= "<td>" . $service_affected . "</td>";
				$body .= "<td>" . $sintoma . "</td>";
				$body .= "<td>" . $causa . "</td>";
				$body .= "<td>" . $solucao . "</td>";				
				$body .= "</tr>";

			}
		}	

	}@_ ;

	
	return $body;	


}

sub sendEmail{
 	
 	my @data = @_ ;
	my $to = 'kevin.testa@horizonstelecom.com';
	my $cc = 'kevin.testa@horizonstelecom.com; ';
	my $from = 'sistemas.oss@horizonstelecom.com';
	my $subject = 'Unidades com tickets recentes';
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