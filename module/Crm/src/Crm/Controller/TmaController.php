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


class TmaController extends AbstractActionController
{   
    
	protected $crmTicketTable;

    public function indexAction()
    {

        $firstDate = date('Y/m/d', strtotime("-10 days")); 
        $lastDate = date('Y/m/d'); 

        $viewModel = new ViewModel( array(
            'firstDate' => $firstDate, 
            'lastDate' => $lastDate
        ));
        return $viewModel;  
        
    }

    public function queueAction()
    {

        $firstDate = date('Y/m/d', strtotime("-10 days")); 
        $lastDate = date('Y/m/d'); 

        $viewModel = new ViewModel( array(
            'firstDate' => $firstDate, 
            'lastDate' => $lastDate
        ));
        return $viewModel;  
        
    }

    //GERAL PAGE
    public function ticketListAction(){
       
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $ticketList = $this->getTables('Crm\Model\TmaTicket')->ticketList($postData, 1, 10);

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

    public function totalTimeAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->totalTime($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotal'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values

        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function totalTimeAcionamentoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->totalTimeAcionamento($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotal'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function totalTimeAtendimentoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->totalTimeAtendimento($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotal'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function timeFilaAtendimentoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->timeFilaAtendimento($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tma_total_atendimento'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function timeFilaAtendimentoTecnicoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->timeFilaAtendimentoTecnico($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tma_total_atendimento_tecnico'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    
    
}
