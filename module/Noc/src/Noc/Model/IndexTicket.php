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
            'driver'         => 'Pdo',
            'dsn'            => 'mysql:dbname=oss;host=10.100.0.3',
            'username'       => 'qep',
            'password'       => 'ririgoni',
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
                    CONCAT( MONTHNAME(closetime), '/', YEAR(closetime) ) AS closetime, 
                    MONTH(closetime) AS closeMonth, 
                    YEAR(closetime) AS closeYear,
                    service_affected
                FROM otrs_ticket_summary
                WHERE 
                    type = 'incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                GROUP BY 
                    MONTHNAME(closetime) || '/' || YEAR(closetime) , closeMonth, closeYear, service_affected 
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
                    CONCAT( DAY(closetime), '/', MONTH(closetime) ) AS closetime, 
                    DAY(closetime) AS closeDay, 
                    MONTH(closetime) AS closeMonth,
                    service_affected
                FROM otrs_ticket_summary
                WHERE 
                    type = 'incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                GROUP BY 
                    DAY(closetime) || '/' || MONTH(closetime) , closeMonth, closeDay, service_affected 
                ORDER BY closeMonth ASC, closeDay ASC";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
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
                    type = 'incident' AND 
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
                    type = 'incident' AND 
                    ts = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    service_affected = 'Nao'
                GROUP BY causa
                ORDER BY total DESC 
                LIMIT 10";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;

    }
    
}