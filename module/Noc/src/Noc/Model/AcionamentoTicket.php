<?php
namespace Noc\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class AcionamentoTicket
{   

    public function connection()
    {
        $db = new DbAdapter(
        array(
            'driver'        => 'Pdo',
            'dsn'            => 'pgsql:dbname=oss;host=10.100.0.37',
            'username'       => 'oss',
            'password'       => 'httoss',      
            )
        );
        return $db;
    }


    public function ticketList($filter)
    {   
        $db = $this->connection();

        $sql = "SELECT * 
                FROM otrs_ticket_summary 
                WHERE ".
                    $filter['where'] ;

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeAcionamento($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS volumeacionamento,
                    (SELECT 
                        COUNT(*) 
                    FROM otrs_ticket_summary 
                    WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate') as volumetotal
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (forjintel+constel+httcuritiba+httmaua+httosasco) > 0
                ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;

    }

    public function volumeAcioAfeta($filter)
    {
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS volumeafeta,
                    (
                        SELECT 
                            COUNT(*) AS volumeafeta
                        FROM otrs_ticket_summary 
                        WHERE 
                            type = 'Incident' AND 
                            ts = 'closed successful' AND 
                            closetime >= '$firstDate' AND 
                            closetime <= '$lastDate' AND 
                            (forjintel+constel+httcuritiba+httmaua+httosasco) > 0 AND 
                            service_affected = 'Não'
                    ) as volumenaoafeta 
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (forjintel+constel+httcuritiba+httmaua+httosasco) > 0 AND 
                    service_affected = 'Sim'

                ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeAcioArea($filter)
    {
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    constel, forjintel, httcuritiba, httosasco, httmaua
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (forjintel+constel+httcuritiba+httmaua+httosasco) > 0 
                ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeAcioAreaNAfeta($filter)
    {
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    constel, forjintel, httcuritiba, httosasco, httmaua
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (forjintel+constel+httcuritiba+httmaua+httosasco) > 0 AND 
                    service_affected = 'Não'
                ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeAcioAreaAfeta($filter)
    {
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    constel, forjintel, httcuritiba, httosasco, httmaua
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (forjintel+constel+httcuritiba+httmaua+httosasco) > 0 AND 
                    service_affected = 'Sim'
                ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    
}