<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Noc;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;

use Noc\Model\AcionamentoTicket;
use Noc\Model\IndexTicket;
use Noc\Model\TmaTicket;
use Noc\Model\Campo;
use Noc\Model\GestaoGeral;
use Crm\Model\App;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Ldap as AuthAdapter;    

use Zend\ModuleManager\Listener;


class Module
{

    public function onBootstrap(MvcEvent $e) 
    {
        //ini_set('display_startup_errors',false);
        //ini_set('display_errors',false);
        
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);


       // $this->checkAuthentication($eventManager);
        
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
                 'Noc\Model\IndexTicket' =>  function($sm) {
                     $table = new IndexTicket;
                     return $table;
                 },
                 'Noc\Model\AcionamentoTicket' =>  function($sm) {
                     $table = new AcionamentoTicket;
                     return $table;
                 },
                 'Noc\Model\TmaTicket' =>  function($sm) {
                     $table = new TmaTicket;
                     return $table;
                 },
                 'Noc\Model\Campo' =>  function($sm) {
                     $table = new Campo;
                     return $table;
                 },
                 'Noc\Model\GestaoGeral' =>  function($sm) {
                     $table = new GestaoGeral;
                     return $table;
                 },
                 'Noc\Model\Alarmes' =>  function($sm) {
                     $table = new Alarmes;
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
