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



class IndexController extends AbstractActionController
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

    public function ticketListAction(){
       
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $ticketList = $this->getTables('Crm\Model\IndexTicket')->ticketList($postData);

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

            // return variavel de classe com service ModelContato
            return $this->crmTicketTable;
        }
    }

    public function volumeMesAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $closeVolumeTicket = $this->getTables('Crm\Model\IndexTicket')->ticketsFechados($postData);

        $closeLegend = NULL;
        $closeValues = NULL;
        foreach ($closeVolumeTicket['closeResults'] as $data) {
            $closeLegend .= "'".$data['closetime']."',";
            $closeValues .= $data['total'].",";
        }

        $openLegend = NULL;
        $openValues = NULL;
        foreach ($closeVolumeTicket['openResults'] as $data) {
            $openLegend .= "'".$data['opentime']."',";
            $openValues .= $data['total'].",";
        }

        $viewModel = new ViewModel(array(
            'closeLegend' => $closeLegend,
            'closeValues' => $closeValues,
            'openLegend' => $openLegend,
            'openValues' => $openValues
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }
#s

    public function volumeDiaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->ticketsFechadosDia($postData);

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
            $openLegend = $data['opentime'];
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

    public function volumeTipoAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->volumeTipo($postData);

        $valuesGraph = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['type'];
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

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->volumeAcionamento($postData);

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

    public function volumeCidadeAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->volumeCidade($postData);

        $valuesGraph = NULL;
        $city = Array();
        $volumeCTA = 0;
        $volumeOCO = 0;
        $volumeMUA = 0;
        $volumeSJC = 0;
        foreach ($volumeTicket as $data) {

            $city[] = $data['city'];
            if (preg_grep('/CTA|PNI|ATT|BCU|AUC/', $city)) {    
                $volumeCTA = $volumeCTA +  $data['volume'];
            }
            else if (preg_grep('/OCO|BRE/', $city)) {    
                $volumeOCO = $volumeOCO +  $data['volume'];
            } 
            else if (preg_grep('/MUA/', $city)) {    
                $volumeMUA = $volumeMUA +  $data['volume'];
            } 
            else if (preg_grep('/SJC/', $city)) {    
                $volumeSJC = $volumeSJC +  $data['volume'];
            } 
            unset($city);
            
        }

        $values = $volumeCTA + $volumeOCO + $volumeMUA + $volumeSJC;
        
        $valuesGraphCTA = "['CTA',".$volumeCTA."],";
        $valuesGraphOCO = "['OCO',".$volumeOCO."],";
        $valuesGraphMUA = "['MUA',".$volumeMUA."],";
        $valuesGraphSJC = "['SJC',".$volumeSJC."]";
        
        $viewModel = new ViewModel(array(
            'valuesGraphCTA' => $valuesGraphCTA ,
            'valuesGraphOCO' => $valuesGraphOCO ,
            'valuesGraphMUA' => $valuesGraphMUA ,
            'valuesGraphSJC' => $valuesGraphSJC ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
      
    }

    public function volumeCausaAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->volumeCausa($postData);

        $legend = NULL;
        $values = NULL;
        foreach ($volumeTicket as $data) {
            $legend .= "'".trim( $data['causa'] ) ."',";
            $values .= $data['volume'].",";
        }
        
        $viewModel = new ViewModel(array(
            'legend' => $legend,
            'values' => $values
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }


    //ACIONAMENTO PAGE
    public function tmaTotalTimeAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\IndexTicket')->TmaTotalTime($postData);

        $valuesGraph = NULL;
        foreach ($volumeTicket as $data) {
            $legend = $data['tmatotal'];
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
