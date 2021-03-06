<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Crm\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class CustomerController extends AbstractActionController
{   
    
	protected $crmTicketTable;

    public function indexAction()
    {
        $firstDate = date('Y/m/d', strtotime("-10 days")); 
        $lastDate = date('Y/m/d'); 
        $types = $this->getTables('Crm\Model\IndexTicket')->types();

        $viewModel = new ViewModel( array(
            'firstDate' => $firstDate, 
            'lastDate' => $lastDate, 
            'types' => $types            
        ));
        return $viewModel; 
        
    }

    //GERAL PAGE
    public function ticketListAction(){
       
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $ticketList = $this->getTables('Crm\Model\CustomerTicket')->ticketList($postData);
        $viewModel = new ViewModel(array(
            'ticketList' => $ticketList,
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

    public function volumeClienteAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\CustomerTicket')->volumeCliente($postData);

        $legend = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend .= "'".$data['customer']."',";
            $values .= $data['volume'].",";
        }
        
        $viewModel = new ViewModel(array(
            'legend' => $legend,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeServiceAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\CustomerTicket')->volumeService($postData);

        $legend = NULL;
        $values = NULL;
        $customerName = NULL;
        foreach ($volumeTicket as $data) {
            $legend .= "'".$data['service']."',";
            $values .= $data['volume'].",";
            $customerName .= "'".$data['service']."': '".$data['customer_name']."' ,";
        }

        $viewModel = new ViewModel(array(
            'legend' => $legend,
            'values' => $values,
            'customerName' => $customerName
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeProdutoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\CustomerTicket')->volumeProduto($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['product'];
            $values = $data['volume'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }
    
}
