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
	$html .= "<th>Sintoma</th>";
	$html .= "<th>Causa</th>";
	$html .= "<th>Solucao</th>";
	$html .= "</tr>";

my $dbh = DBI->connect('DBI:Pg:dbname=oss;host=10.100.0.37;port=5432', 'oss', 'httoss') 
		or die "Connect error  $DBI::errstr\n";

$html .= &tickets;

$html .= "</table>";

#print $html;
#&sendEmail( $html );


sub tickets{

	my $date = strftime "%Y-11", localtime;	
	my $date2 = strftime "%Y-%m-%d", localtime;	
	my $body = '' ;

	my $sth = $dbh->prepare( "SELECT 
								tn, totaltime, service_affected,title, opentime, closetime, sintoma, causa, solucao, customers_affected 
							FROM otrs_ticket_summary 
							WHERE 
								  closetime like '$date%' AND 
								  causa NOT LIKE '%Rede do Cliente%'
							ORDER BY opentime ASC");

	$sth->execute();

	my @ticketsArr = ();
	my $ticket = {};
	my @repeat = {};

	while ( my @row = $sth->fetchrow_array ){
		my ( $tn, $totaltime, $service_affected, $title, $opentime, $closetime, $sintoma, $causa, $solucao, $customers_affected ) = @row;
		my $rows = $sth->rows;
		my $days = int($totaltime/(24*60*60));
		my $hours = ($totaltime/(60*60))%24;
		my $mins = ($totaltime/60)%60;

		my @customers = $customers_affected =~ /(\w{3,4}-(?:C-)?\d+[-\w\d]*)/g;
		foreach my $customer (@customers) {

			$ticket = {
				$customer => {
					service => $customer ,
					tn => $tn,
					opentime => $opentime
				}
			};

			if ( exists($ticketsArr[@_]{$customer}) ) {
				push @repeat, $ticket;
				print 'repeat';
			}

			push @ticketsArr, $ticket;

		#print $customer." ". $tn;
		#print "\n";
		}
	}	
		#print Dumper $ticketsArr[@_]{'CTA-0083W'}{service};
		print Dumper @ticketsArr;
	

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