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


class AlarmesController extends AbstractActionController
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

    //GERAL PAGE
    public function ticketListAction(){
       
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $ticketList = $this->getTables('Noc\Model\TmaTicket')->ticketList($postData, 1, 10);

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

    public function noTreatmentAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeAlarmes = $this->getTables('Noc\Model\Alarmes')->noTreatment($postData);

        $valuesGraph = NULL;
        foreach ($volumeAlarmes as $data) {
            $legend = $data['tmatotalstring'];
            $values = $data['total'];

            $valuesGraph .= "['".$legend."',".$values."],";
        }
        $viewModel = new ViewModel(array(
            'values' => $valuesGraph ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    
}
