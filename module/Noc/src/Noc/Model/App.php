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
}