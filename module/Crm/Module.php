<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Crm;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;

use Crm\Model\CustomerTicket;
use Crm\Model\IndexTicket;
use Crm\Model\TmaTicket;
use Crm\Model\App;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Ldap as AuthAdapter;    
use Zend\Session\SessionManager;

use Zend\ModuleManager\Listener;


class Module
{

    public function onBootstrap(MvcEvent $e) 
    {
        //ini_set('display_startup_errors',false);
        //ini_set('display_errors',false);
        $this->initSession();
       
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);

        $this->checkAuthentication($eventManager);


    }

    public function initSession()
    {
        $sessionManager = new SessionManager;
        $sessionManager->start();

        if ( empty( $_SESSION['date'] ) ){
            $_SESSION['date'] = time();
        }
        else  {
            $time = time() - $_SESSION['date'];

            if( $time > 1800 ){
                $auth = new AuthenticationService();
                $auth->clearIdentity();
                $_SESSION['date'] = time();
            }
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getServiceConfig()
    {
        return array(
             'factories' => array(
                 'Crm\Model\IndexTicket' =>  function($sm) {
                     $table = new IndexTicket;
                     return $table;
                 },
                 'Crm\Model\CustomerTicket' =>  function($sm) {
                     $table = new CustomerTicket;
                     return $table;
                 },
                 'Crm\Model\TmaTicket' =>  function($sm) {
                     $table = new TmaTicket;
                     return $table;
                 },
                'Crm\Model\App' =>  function($sm) {
                     $table = new App;
                     return $table;
                 },
             ),
         );
    }

    public function checkAuthentication($eventManager)
    {
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($e) { 
            
            $routeMatch = $e->getRouteMatch(); 

            $controller = $routeMatch->getParam('controller'); //nome do controller 
            $action     = $routeMatch->getParam('action'); //nome da action
            $auth = new AuthenticationService();

            if($action == 'logout'){ //Se a action 'logout' ser acionada, limpa a session de autenticação
                $auth->clearIdentity();
            }

            if( $controller != 'app' && ( $action != 'login' && $action != 'auth' ) ) {
            
                if( !$auth->getIdentity() ){//Se não estiver logado, redireciona para a tela de login
                    $response = $e->getResponse();
                    $response->getHeaders()->addHeaderLine('Location', '../app/login');
                    $response->setStatusCode(302);                
                }

            }
            
        }); 
    }
}
