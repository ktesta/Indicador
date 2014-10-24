<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Noc\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Ldap\Ldap;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Ldap as AuthAdapter;    
use Zend\Mvc\Controller\Plugin\Redirect;

class AppController extends AbstractActionController
{   
    public $username, $crmTicketTable;
	protected $status;
    
    public function mainAction()
    {

        $viewModel = new ViewModel();
        return $viewModel; 
        
    }

    //GERAL PAGE
    public function ticketListAction(){      

        $ticketList = $this->getTables('Crm\Model\CustomerTicket')->ticketList($postData, 1, 10);

        $viewModel = new ViewModel(array(
            'ticketList' => $ticketList
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function getTables( $table )
    {
        // adicionar service ModelContato a variavel de classe
        if (!$this->crmTicketTable){
            $this->crmTicketTable = $this->getServiceLocator()->get($table);

            // return vairavel de classe com service ModelContato
            return $this->crmTicketTable;
        }
    }

    public function loginAction()
    {   

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;

    }

    public function authAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();
        $user = $postData['login'];
        $password = $postData['password'];

        $username = "uid=$user,ou=horizons, ou=people, dc=htt, dc=com";
        $connectLdap = array(
            'host'     => 'ldap://10.100.0.40/',
            'port'     => 389,
            'username' => $username,
            'password' => $password,
            'baseDn'   => 'dc=htt,dc=com'
        );

        $auth = new AuthenticationService();
        $adapter = new AuthAdapter(
            array('server'=>
                $connectLdap
            ),
            $username, 
            $password
        );

        $result = $auth->authenticate($adapter);

        if ( $auth->hasIdentity() ){
            $ldap= new Ldap($connectLdap);
            $user = $ldap->getEntry($username);
            //var_dump($user);
            
            $auth->getStorage()->write($user);
            
            $username = $user['cn'][0];
            $ticketList = $this->getTables('Crm\Model\App')->log($username);

            $this->status = 1;

        }
        else{
            $this->status = 0;
        }

        $viewModel = new ViewModel(array('status' => $this->status) ) ;
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function logoutAction()
    {

    }
}
