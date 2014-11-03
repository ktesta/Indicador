<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class CustomerTicket
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

    public function volumeCliente($filter)
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
                    COUNT(*) as volume,
                    customer
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' 
                    $sqlType 
                GROUP BY customer
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
                    COUNT(*) as volume,
                    product
                FROM crm_otrs_ticket_summary 
                WHERE 
                    status = 'closed successful' AND 
                    closetime >= '$firstDate' AND 
                    closetime <= '$lastDate' AND 
                    product <> '' 
                    $sqlType 
                GROUP BY product
                 ";
        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }
}