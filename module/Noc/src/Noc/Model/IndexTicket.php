<?php
namespace Noc\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\Driver\Pdo\Pdo;

class IndexTicket
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

    public function ticketsFechados($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    EXTRACT('MONTH' FROM closetime::date) || '/' || EXTRACT('YEAR' FROM closetime::date)  AS closetime, 
                    EXTRACT('MONTH' FROM closetime::date) AS closeMonth, 
                    EXTRACT('YEAR' FROM closetime::date) AS closeYear,
                    service_affected
                FROM otrs_ticket_summary
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                GROUP BY 
                    EXTRACT('MONTH' FROM closetime::date) || '/' || EXTRACT('YEAR' FROM closetime::date) , closeMonth, closeYear, service_affected 
                ORDER BY closeYear ASC, closeMonth ASC";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function ticketsFechadosDia($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    DATE(closetime) AS closetime 
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    service_affected = 'Sim' AND
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                GROUP BY DATE(closetime) 
                ORDER BY closetime ASC";

        $stmt = $db->query($sql);
        $closeResults = $stmt->execute();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    DATE(closetime) AS closetime 
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    service_affected = 'Não' AND
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                GROUP BY DATE(closetime) 
                ORDER BY closetime ASC";

        $stmt = $db->query($sql);
        $openResults = $stmt->execute();
        
        $results = Array( 'closeResults' => $closeResults ,
                          'openResults' => $openResults );

        return $results;



    }

    public function volumeCausaAfeta($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total,
                    causa  
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    service_affected = 'Sim'
                GROUP BY causa
                ORDER BY total DESC 
                LIMIT 10";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;

    }

    public function volumeCausaNaoAfeta($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total,
                    causa  
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    service_affected = 'Não'
                GROUP BY causa
                ORDER BY total DESC 
                LIMIT 10";

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
    
}