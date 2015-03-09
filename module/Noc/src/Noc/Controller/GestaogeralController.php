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


class GestaogeralController extends AbstractActionController
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

        $ticketList = $this->getTables('Noc\Model\GestaoGeral')->ticketList($postData);
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

    public function ticketsDiaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->ticketsDia($postData);

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
            $openLegend = $data['opentime'];
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

    public function volumeAbertoAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->volumeAberto($postData);

        $color = Array(
            'asell' => '#488AC7',
            'iserafim' => '#488AC7',
            'kalberti' => '#488AC7',
            'emessias' => '#488AC7',
            'mbordinhao' => '#488AC7',
            'agross' => '#488AC7',
            
            'floesch' => 'orange',
            'mcaviglia' => 'orange',
            'spinheiro' => 'orange',
            'lrivatto' => 'orange',

            'ealves' => 'brown',
            'asantos' => 'brown',
            'ctoshio' => 'brown',
            'rvaz' => 'brown',
            
            'lbueno' => 'purple',
            'rkowalski' => 'purple',
        );

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['openby'];
            $values = $data['total'];
            ( !empty($color[$legend]) ) ? $colorColumn = $color[$legend] : $colorColumn = 'black';

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $colorColumn ."'},";
        }

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeFechadoAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->volumeFechado($postData);

        $color = Array(
            'asell' => '#488AC7',
            'iserafim' => '#488AC7',
            'kalberti' => '#488AC7',
            'emessias' => '#488AC7',
            'mbordinhao' => '#488AC7',
            
            'floesch' => 'orange',
            'mcaviglia' => 'orange',
            'agross' => 'orange',
            'spinheiro' => 'orange',

            'ealves' => 'brown',
            'asantos' => 'brown',
            'lrivatto' => 'brown',
            'ctoshio' => 'brown',
            'rvaz' => 'brown',
            
            'lbueno' => 'purple',
            'rkowalski' => 'purple',
        );

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['closeby'];
            $values = $data['total'];
            ( !empty($color[$legend]) ) ? $colorColumn = $color[$legend] : $colorColumn = 'black';

            $valuesGraph .= "{name:'".$legend."', y:".$values.", color: '". $colorColumn ."'},";
        }

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function ticketsHCAction()
    {

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->ticketsHC($postData);

        $valuesGraph = NULL;
        $hc = 0;
        $fhc = 0;
        foreach ($volumeTicket as $data) {
            $hc = $data['hc'];
            $fhc = $data['total'] - $data['hc'];
        }
        
        $valuesGraph .= "['Horário comercial',".$hc."],";
        $valuesGraph .= "['Fora do horário comercial',".$fhc."],";
        
        $viewModel = new ViewModel(array(
            'graph' => $valuesGraph ,
            'values' => $hc + $fhc ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
      
    }

    public function volumeHCAction()
    {

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->volumeHC($postData);

        $valuesGraph = NULL;
        $afeta = 0;
        $nafeta = 0;
        foreach ($volumeTicket as $data) {

            ( !empty($data['service_affected']) && $data['service_affected'] == 'Sim' ) ? $afeta = $data['volume'] : '';
            ( !empty($data['service_affected']) && $data['service_affected'] == 'Não' ) ? $nafeta = $data['volume'] : '';

        }
        
        $valuesGraph .= "{name: 'Afeta serviço', y:".$afeta.", color: '#B22222' },";
        $valuesGraph .= "{name: 'Não afeta serviço', y:".$nafeta.", color: '#228B22' }"; 
        
        $viewModel = new ViewModel(array(
            'graph' => $valuesGraph ,
            'values' => $afeta + $nafeta ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
      
    }

    public function volumeForaHCAction()
    {

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\GestaoGeral')->volumeForaHC($postData);

        $valuesGraph = NULL;
        $afeta = 0;
        $nafeta = 0;
        foreach ($volumeTicket as $data) {

            ( !empty($data['service_affected']) && $data['service_affected'] == 'Sim' ) ? $afeta = $data['volume'] : '';
            ( !empty($data['service_affected']) && $data['service_affected'] == 'Não' ) ? $nafeta = $data['volume'] : '';

        }
        
        $valuesGraph .= "{name: 'Afeta serviço', y:".$afeta.", color: '#B22222' },";
        $valuesGraph .= "{name: 'Não afeta serviço', y:".$nafeta.", color: '#228B22' }"; 
        
        $viewModel = new ViewModel(array(
            'graph' => $valuesGraph ,
            'values' => $afeta + $nafeta ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
      
    }

    
}
