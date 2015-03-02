<?php
namespace Noc\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class Alarmes
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

    public function noTreatment($filter) 
    {
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();
        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotalstring 
                FROM otrs_ticket_summary 
                WHERE 
                    type = 'Incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    service_affected = 'Sim'
                GROUP BY tmatotalstring ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();

        return $results;
    }

    

}