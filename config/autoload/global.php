<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db'=> array(
        'adapters'=>array(
            'PSQL_OSS' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'pgsql:dbname=oss;host=10.100.0.37',
                'username' => 'oss',
                'password' => 'httoss'
            ),
            'PSQL_AHS' => array(
                'driver'         => 'Pdo',
                'dsn'            => 'mysql:dbname=alerts;host=10.100.0.2',
                'username' => 'oss',
                'password' => 'httoss'
            ),
        )
    ),
    'service_manager' => array(
    'abstract_factories' => array(
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
        )
    ),
    'module_layouts' => array(
       'Crm' => 'layout/layoutCrm.phtml',
       'Noc' => 'layout/layoutNoc.phtml',
   ),
);
