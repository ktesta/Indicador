        <?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    
    'router' => array(
        'routes' => array(
            'nocMain' => array(
                'type'      => 'Literal',
                'options'   => array(
                    'route'    => '/noc/index/index',
                    'defaults' => array(
                        'controller' => 'Noc\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'noc' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/noc',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Noc\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),            
        ),
    ),

    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Noc\Controller\Index' => 'Noc\Controller\IndexController',
            'Noc\Controller\App' => 'Noc\Controller\AppController',
            'Noc\Controller\Acionamento' => 'Noc\Controller\AcionamentoController',
            'Noc\Controller\Tma' => 'Noc\Controller\TmaController',
            'Noc\Controller\Campo' => 'Noc\Controller\CampoController',
            'Noc\Controller\Gestaogeral' => 'Noc\Controller\GestaogeralController',
            'Noc\Controller\Alarmes' => 'Noc\Controller\AlarmesController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions'       => false,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layoutNoc.phtml',
            'ZendSkeletonApplication/index/index' => __DIR__ . '/../view/crm/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
