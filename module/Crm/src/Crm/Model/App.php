<?php
namespace Crm\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;


class App
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

    //Ao logar no sistema, registra no sistema
    public function log($username)
    {   

        $db = $this->connection();

        $sql = "INSERT INTO log_kevin (users,date)  
                VALUES
                    ('$username', localtimestamp ) ";

        $stmt = $db->query($sql);
        $results = $stmt->execute();
        
        return $results;
    }

    //Ajusta query SQL para filtrar pelo tipo do ticket - Gráficos
    public function filterType( $filter )
    {
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

            return $sqlType;
        }
        else{
            return NULL;
        }
    }

    //Ajusta query SQL para filtrar pelo tipo do ticket - Lista de tickets
    public function filterTypeTicketList( $filter )
    {
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

            return $sqlType;
        }
        else{
            return NULL;
        }
    }
}