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

        $sqlType = NULL;
        
        if( !empty( $filter['type'] ) ){
            
            $i = 0; 
            foreach ($filter['type'] as $type){
                
                if( $i == 0){
                    $sqlType= " AND (type = '$type' ";
                }
                else{
                    $sqlType.= " OR type = '$type' ";
                }

                $i++;
            }
            $sqlType.= " ) ";
        }
        
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
        $sqlType = NULL;

        if( !empty( $filter['filter']['type'] ) ){
            
            $i = 0; 
            foreach ($filter['filter']['type'] as $type){
                if( $i == 0){
                    $sqlType= " AND (type = '$type' ";
                }
                else{
                    $sqlType.= " OR type = '$type' ";
                }

                $i++;
            }
            $sqlType.= " ) ";
        }

        $db = $this->connection();
        $sql = "SELECT 
                    COUNT(*) AS total, 
                    tmatotal 
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
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
        $sqlType = NULL;

        if( !empty( $filter['filter']['type'] ) ){
            
            $i = 0; 
            foreach ($filter['filter']['type'] as $type){
                if( $i == 0){
                    $sqlType= " AND (type = '$type' ";
                }
                else{
                    $sqlType.= " OR type = '$type' ";
                }

                $i++;
            }
            $sqlType.= " ) ";
        }

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
        $sqlType = NULL;

        if( !empty( $filter['filter']['type'] ) ){
            
            $i = 0; 
            foreach ($filter['filter']['type'] as $type){
                if( $i == 0){
                    $sqlType= " AND (type = '$type' ";
                }
                else{
                    $sqlType.= " OR type = '$type' ";
                }

                $i++;
            }
            $sqlType.= " ) ";
        }

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
                    $sqlType
                GROUP BY tmatotal ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }

    public function totalTimeType($filter)
    {   
        $firstDate = $filter['filter']['firstDate'];
        $lastDate = $filter['filter']['lastDate'];
        $sqlType = NULL;

        if( !empty( $filter['filter']['type'] ) ){
            
            $i = 0; 
            foreach ($filter['filter']['type'] as $type){
                if( $i == 0){
                    $sqlType= " AND (type = '$type' ";
                }
                else{
                    $sqlType.= " OR type = '$type' ";
                }

                $i++;
            }
            $sqlType.= " ) ";
        }

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
                    $sqlType
                GROUP BY tmatotal,type ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
       
    }
}