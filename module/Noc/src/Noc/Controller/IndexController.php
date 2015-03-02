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



class IndexController extends AbstractActionController
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

    public function ticketListAction(){
       
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $ticketList = $this->getTables('Noc\Model\IndexTicket')->ticketList($postData);

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

    public function volumeMesAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\IndexTicket')->ticketsFechados($postData);

        $legend = NULL;
        $valuesAffected = NULL;
        $valuesNotAffected = NULL;
        $closetime = NULL;
        foreach ($volumeTicket as $data) {

            if( $data['closetime'] != $closetime ){
                $legend .= "'".$data['closetime']."',";
            }
            
            if ($data['service_affected'] == 'Sim'){
                $valuesAffected .= $data['total'].",";
            }else{
                $valuesNotAffected .= $data['total'].",";
            }

            $closetime = $data['closetime'];

        }

        $viewModel = new ViewModel(array(
            'legend' => $legend,
            'valuesAffected' => $valuesAffected,
            'valuesNotAffected' => $valuesNotAffected
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeDiaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\IndexTicket')->ticketsFechadosDia($postData);

        $dayNumber = strtotime( $postData['filter']['lastDate'] ) - strtotime( $postData['filter']['firstDate'] );
        $dayNumber = floor($dayNumber / (60 * 60 * 24));

        $date = $postData['filter']['firstDate'];
        $legend = NULL;
        $close = NULL;
        $open = NULL;

        foreach ($volumeTicket['closeResults'] as $data) {
            $closeLegend = $data['closetime'];
            $closeValues[$closeLegend] = $data['total'];
        }

        foreach ($volumeTicket['openResults'] as $data) {
            $openLegend = $data['closetime'];
            $openValues[$openLegend] = $data['total'];
        }

        for( $i = 0; $i < $dayNumber; $i++ ){

            $date = date('Y-m-d', strtotime("+1 days",strtotime($date)));
            $closeValues[$date] = (!empty( $closeValues[$date] )) ? $closeValues[$date] : '0';
            $openValues[$date] = (!empty( $openValues[$date] )) ? $openValues[$date] : '0';
            
            $legend .= "'" . $date . "',";
            $close .= $closeValues[$date] . ",";  
            $open .= $openValues[$date] . ",";  

        }

        $viewModel = new ViewModel(array(
            'legend' => $legend,
            'close' => $close,
            'open' => $open,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }

    

    public function volumeCausaAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\IndexTicket')->volumeCausaAfeta($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['causa'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeCausaNaoAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\IndexTicket')->volumeCausaNaoAfeta($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['causa'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeAcionamentoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\IndexTicket')->volumeAcionamento($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $volumeTotal = $data['volumetotal'];
            $volumeAcionamento = $data['volumeacionamento'];
            $volumeSemAcionamento = $volumeTotal - $volumeAcionamento;

            $valuesGraph .= "['Volume sem acionamentos',".$volumeSemAcionamento."],";
            $valuesGraph .= "['Volume com acionamentos',".$volumeAcionamento."],";
        }
        
        $viewModel = new ViewModel(array(
            'graph' => $valuesGraph ,
            'values' => $volumeTotal ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
      
    }

    
}
