<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use AppController;

class TmaTicket
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

    public function totalTime($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);        

        $db = $this->connection();
        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  
                    $sqlType
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();

        return $results;
       
    }

    public function totalTimeAcionamento($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);        

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    tratamento_tecnico + binario > 0
                    $sqlType
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function totalTimeAtendimento($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);
        
        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    (tratamento_tecnico + binario) = 0
                    $sqlType
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function timeFilaAtendimento($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);
        
        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tma_total_atendimento
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    tma_total_atendimento <> ''
                    $sqlType
                GROUP BY tma_total_atendimento ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function timeFilaAtendimentoTecnico($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = $this->app->filterType($filter);
        
        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tma_total_atendimento_tecnico
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate 00:00:00' AND 
                    closetime <= '$lastDate 23:59:59'  AND 
                    tma_total_atendimento_tecnico <> ''
                    $sqlType
                GROUP BY tma_total_atendimento_tecnico ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }


}