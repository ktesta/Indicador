<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

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
                FROM crm_otrs_ticket_summary 
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
                    to_char(to_timestamp (EXTRACT(month from closetime)::text, 'MM'), 'TMmon') || '/' || EXTRACT(YEAR from closetime) AS closetime, 
                    extract(month from closetime) AS closeMonth, 
                    extract(year from closetime) AS closeYear 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                GROUP BY 
                    to_char(to_timestamp (EXTRACT(month from closetime)::text, 'MM'), 'TMmon') || '/' || EXTRACT(YEAR from closetime) , closeMonth, closeYear 
                ORDER BY closeYear ASC, closeMonth ASC";

        $stmt = $db->query($sql);
        $closeResults = $stmt->execute();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    to_char(to_timestamp (EXTRACT(month from opentime)::text, 'MM'), 'TMmon') || '/' || EXTRACT(YEAR from opentime) AS opentime, 
                    extract(month from opentime) AS openMonth, 
                    extract(year from opentime) AS openYear 
                FROM crm_otrs_ticket_summary 
                WHERE                     
                    opentime >= '$firstDate' AND 
                    opentime <= '$lastDate' 
                GROUP BY 
                    to_char(to_timestamp (EXTRACT(month from opentime)::text, 'MM'), 'TMmon') || '/' || EXTRACT(YEAR from opentime) , openMonth, openYear 
                ORDER BY openYear ASC, openMonth ASC";

        $stmt = $db->query($sql);
        $openResults = $stmt->execute();
        
        $results = Array( 'closeResults' => $closeResults ,
                          'openResults' => $openResults );

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
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate'
                GROUP BY DATE(closetime) 
                ORDER BY closetime ASC";

        $stmt = $db->query($sql);
        $closeResults = $stmt->execute();

        $sql = "SELECT 
                    COUNT(*) AS total, 
                    DATE(opentime) AS opentime 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    opentime >= '$firstDate' AND 
                    opentime <= '$lastDate'
                GROUP BY DATE(opentime) 
                ORDER BY opentime ASC";

        $stmt = $db->query($sql);
        $openResults = $stmt->execute();
        
        $results = Array( 'closeResults' => $closeResults ,
                          'openResults' => $openResults );

        return $results;

    }

    public function volumeTipo($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS total,
                    type  
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                GROUP BY type";

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
                    COUNT(*) AS volumeTotal, 
                    (SELECT 
                        COUNT(*) 
                    FROM crm_otrs_ticket_summary 
                    WHERE 
                        tratamento_tecnico + binario > 0 and 
                        status = 'closed successful' and 
                        closetime >= '$firstDate' and 
                        closetime <= '$lastDate' ) AS volumeAcionamento 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate'";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;      

    }

    public function volumeCidade($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS volume, 
                    SUBSTRING(customer from 1 for 3) as city
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate'
                GROUP BY city ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;      

    }

    public function volumeCausa($filter)
    {   

        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];

        $db = $this->connection();

        $sql = "SELECT 
                    COUNT(*) AS volume, 
                    causa
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    causa <> ''
                GROUP BY causa 
                ORDER BY volume DESC 
                LIMIT 10";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;      

    }
    //ACIONAMENTO PAGE 
    public function TmaTotalTime($filter)
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


    
}