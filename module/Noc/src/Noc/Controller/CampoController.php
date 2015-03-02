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


class CampoController extends AbstractActionController
{   
    
	protected $crmTicketTable;

    protected $color = array( 'Até 4 horas'         => '#228B22',
                              'De 4 a 8 horas'      => '#E6DC22',
                              'De 8 a 12 horas'     => '#F79D01',
                              'Acima de 12 horas'   => '#B22222' );

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

        $ticketList = $this->getTables('Noc\Model\Campo')->ticketList($postData);
        $viewModel = new ViewModel(array(
            'ticketList' => $ticketList,
            //'name' => $ticketList['name']
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

    public function volumeMesAction()
    {

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\Campo')->volumeMes($postData);

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

        $volumeTicket = $this->getTables('Noc\Model\Campo')->volumeDia($postData);

        
        $dayNumber = strtotime( $postData['filter']['lastDate'] ) - strtotime( $postData['filter']['firstDate'] );
        $dayNumber = floor($dayNumber / (60 * 60 * 24));

        $date = $postData['filter']['firstDate'];
        $values = 0;
        $legend = NULL;
        $close = NULL;
        $open = NULL;

        foreach ($volumeTicket['closeResults'] as $data) {
            $closeLegend = $data['closetime'];
            $closeValues[$closeLegend] = $data['total'];

            $values = 1;
        }

        foreach ($volumeTicket['openResults'] as $data) {
            $openLegend = $data['closetime'];
            $openValues[$openLegend] = $data['total'];

            $values = 1;
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
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }

    public function timeAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\Campo')->timeAfeta($postData);

        $valuesGraph = NULL;
        $values = 0;

        for ($i = 0; $i <= 4; $i++){
            $volume[$i] = 0;
        }

        foreach ($volumeTicket as $data) {
            $timetotal = $data['timetotal'];
            $volume[$timetotal] = $data['volume'];

            $values = 1;
        }

        $valuesGraph .= "{name: 'Até 4 horas', y:".$volume[1].", color:'". $this->color['Até 4 horas'] . "'},";
        $valuesGraph .= "{name: 'De 4 a 8 horas', y:".$volume[2].", color:'". $this->color['De 4 a 8 horas'] . "'},";
        $valuesGraph .= "{name: 'De 8 a 12 horas', y:".$volume[3].", color:'". $this->color['De 8 a 12 horas'] . "'},";
        $valuesGraph .= "{name: 'Acima de 12 horas', y:".$volume[4].", color:'". $this->color['Acima de 12 horas'] . "'},";
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }

    public function timeNAfetaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\Campo')->timeNAfeta($postData);

        $valuesGraph = NULL;
        $values = 0;

        for ($i = 0; $i <= 4; $i++){
            $volume[$i] = 0;
        }

        foreach ($volumeTicket as $data) {
            $timetotal = $data['timetotal'];
            $volume[$timetotal] = $data['volume'];

            $values = 1;
        }

        $valuesGraph .= "{name: 'Até 4 horas', y:".$volume[1].", color:'". $this->color['Até 4 horas'] . "'},";
        $valuesGraph .= "{name: 'De 4 a 8 horas', y:".$volume[2].", color:'". $this->color['De 4 a 8 horas'] . "'},";
        $valuesGraph .= "{name: 'De 8 a 12 horas', y:".$volume[3].", color:'". $this->color['De 8 a 12 horas'] . "'},";
        $valuesGraph .= "{name: 'Acima de 12 horas', y:".$volume[4].", color:'". $this->color['Acima de 12 horas'] . "'},";
        
        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }
}
