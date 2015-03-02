<?php
namespace Noc\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class GestaoGeral
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

    public function ticketsDia($filter)
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
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                GROUP BY DATE(closetime) 
                ORDER BY closetime ASC";

        $stmt = $db->query($sql);
        $closeResults = $stmt->execute();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    DATE(opentime) AS opentime 
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    opentime >= '$firstDate 00:00:00' AND 
                    opentime <= '$lastDate 23:59:59' 
                GROUP BY DATE(opentime) 
                ORDER BY opentime ASC";

        $stmt = $db->query($sql);
        $openResults = $stmt->execute();
        
        $results = Array( 'closeResults' => $closeResults ,
                          'openResults' => $openResults );

        return $results;
    }

    public function volumeAberto($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    openby
                FROM otrs_ticket_summary
                WHERE 
                    type = 'Incident' AND 
                    opentime >= '$firstDate 00:00:00' AND 
                    opentime <= '$lastDate 23:59:59' 
                GROUP BY 
                    openby 
                ORDER BY total DESC";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeFechado($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    closeby
                FROM otrs_ticket_summary
                WHERE 
                    type = 'Incident' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                GROUP BY 
                    closeby 
                ORDER BY total DESC";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function ticketsHC($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = " SELECT 
                    count(*) as hc,
                    ( SELECT count(*) FROM otrs_ticket_summary 
                        WHERE 
                            opentime >= '$firstDate 00:00:00' AND 
                            opentime <= '$lastDate 23:59:59' AND 
                            type = 'Incident'                           
                    ) AS total
                FROM otrs_ticket_summary 
                WHERE 
                    opentime >= '$firstDate 00:00:00' AND 
                    opentime <= '$lastDate 23:59:59' AND 
                    type = 'Incident' AND (
                        EXTRACT( dow FROM opentime::timestamp ) IN(1,2,3,4,5) AND (
                            EXTRACT( hour FROM opentime::timestamp ) >= 8 AND 
                            EXTRACT( hour FROM opentime::timestamp ) < 18 
                        )
                    ) ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeHC($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = " SELECT 
                    service_affected, 
                    count(*) volume 
                FROM otrs_ticket_summary 
                WHERE 
                    opentime >= '$firstDate 00:00:00' AND 
                    opentime <= '$lastDate 23:59:59' AND 
                    type = 'Incident' AND (
                        EXTRACT( dow FROM opentime::timestamp ) IN(1,2,3,4,5) AND (
                            EXTRACT( hour FROM opentime::timestamp ) >= 8 AND 
                            EXTRACT( hour FROM opentime::timestamp ) < 18 
                        )
                    ) 
                GROUP BY service_affected ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeForaHC($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = " SELECT 
                    service_affected, 
                    count(*) volume 
                FROM otrs_ticket_summary 
                WHERE 
                    opentime >= '$firstDate 00:00:00' AND 
                    opentime <= '$lastDate 23:59:59' AND 
                    type = 'Incident' AND (
                        EXTRACT( dow FROM opentime::timestamp ) IN(0,6) OR 
                        EXTRACT( hour FROM opentime::timestamp ) < 8 OR 
                        EXTRACT( hour FROM opentime::timestamp ) >= 18 
                    ) 
                GROUP BY service_affected ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }
}



