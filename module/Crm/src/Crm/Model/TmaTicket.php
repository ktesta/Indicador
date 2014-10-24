<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class TmaTicket
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
                FROM crm_otrs_ticket_summary 
                WHERE ".
                    $filter['where'] ;

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function totalTime($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate'
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function totalTimeAcionamento($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    tratamento_tecnico + binario > 0
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function totalTimeAtendimento($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    (tratamento_tecnico + binario) = 0
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function totalTimeType($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    type,
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate'
                GROUP BY tmatotal,type ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }
}