<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use AppController;

class CustomerTicket
{   
    protected $app;

    function __construct()
    {
        $this->app = new App;
    }

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

    public function types()
    {
        $db = $this->connection();
        
        $sql = "SELECT type FROM crm_otrs_ticket_summary GROUP BY type";
        
        $stmt = $db->query($sql);
        $results = $stmt->execute();

        return $results;
    }
    
    public function ticketList($filter)
    {   

        $sqlType = $this->app->filterTypeTicketList($filter);

        $db = $this->connection();

        $sql = "SELECT * 
                FROM crm_otrs_ticket_summary 
                WHERE ".
                    $filter['where'] .
                    $sqlType ;

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeCliente($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);
            
        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) as volume,
                    customer
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  
                    $sqlType 
                GROUP BY customer
                ORDER BY volume DESC
                LIMIT 10
                 ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeService($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);
            
        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) as volume,
                    service,
                    customer_name
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59' 
                    $sqlType 
                GROUP BY service, customer_name
                ORDER BY volume DESC
                LIMIT 10
                 ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    public function volumeProduto($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        
        $sqlType = $this->app->filterType($filter);

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) as volume,
                    product
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    product <> '' 
                    $sqlType 
                GROUP BY product
                 ";
        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

}