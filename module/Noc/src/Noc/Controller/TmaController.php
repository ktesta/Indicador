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


class TmaController extends AbstractActionController
{   
    
	protected $crmTicketTable;
    
    protected $color = array( 'Até 4 horas'         => '#228B22',
                              'De 4 a 8 horas'      => '#E6DC22',
                              'De 8 a 12 horas'     => '#F79D01',
                              'Acima de 12 horas'   => '#B22222' );

    protected $colorTwo = array( 'Até 1 horas'         => '#228B22',
                                  'De 1 a 2 horas'      => '#E6DC22',
                                  'De 2 a 4 horas'     => '#F79D01',
                                  'Acima de 4 horas'   => '#B22222' );

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



    public function totalTimeAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\TmaTicket')->totalTime($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotalstring'];
            $values = $data['total'];
            $color = $this->color[$legend];

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $color ."'},";
        }
        $viewModel = new ViewModel(array(
            'values' => $valuesGraph ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function totalTimeNAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\TmaTicket')->totalTimeNAfeta($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotalstring'];
            $values = $data['total'];
            $color = $this->color[$legend];

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $color ."'},";

        }
        $viewModel = new ViewModel(array(
            'values' => $valuesGraph ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function totalTimeNocAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\TmaTicket')->totalTimeNoc($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotalnocstring'];
            $values = $data['total'];
            $color = $this->colorTwo[$legend];

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $color ."'},";
        }
        $viewModel = new ViewModel(array(
            'values' => $valuesGraph ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function totalTimeNocNAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\TmaTicket')->totalTimeNocNAfeta($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotalnocstring'];
            $values = $data['total'];
            $color = $this->colorTwo[$legend];

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $color ."'},";
        }
        $viewModel = new ViewModel(array(
            'values' => $valuesGraph ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }
}
