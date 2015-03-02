<?php
namespace Noc\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class Campo
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

    public function volumeMes($filter) 
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
                    causa = 'Fibra óptica' AND
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                GROUP BY 
                    EXTRACT('MONTH' FROM closetime::date) || '/' || EXTRACT('YEAR' FROM closetime::date) , closeMonth, closeYear, service_affected 
                ORDER BY closeYear ASC, closeMonth ASC";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeDia($filter)
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
                    causa = 'Fibra óptica' AND
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
                    causa = 'Fibra óptica' AND
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

    public function timeAfeta($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

       $sql = "SELECT count(tn) as volume,        
                CASE 
                  when (totaltime/60)<240 then '1' 
                  when (totaltime/60)>=240 AND (totaltime/60)<480 then '2' 
                  when (totaltime/60)>=480 AND (totaltime/60)<720 then '3'
                  when (totaltime/60)>=720 then '4' 
                end as timetotal  
              FROM otrs_ticket_summary  
              WHERE type='Incident' AND 
                    service_affected = 'Sim' AND  
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' AND 
                    causa = 'Fibra óptica' 
              GROUP BY timetotal" ;

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function timeNAfeta($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

       $sql = "SELECT count(tn) as volume,        
                CASE 
                  when (totaltime/60)<240 then '1' 
                  when (totaltime/60)>=240 AND (totaltime/60)<480 then '2' 
                  when (totaltime/60)>=480 AND (totaltime/60)<720 then '3'
                  when (totaltime/60)>=720 then '4' 
                end as timetotal  
              FROM otrs_ticket_summary  
              WHERE type='Incident' AND 
                    service_affected = 'Não' AND  
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' AND 
                    causa = 'Fibra óptica' 
              GROUP BY timetotal" ;

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }
}