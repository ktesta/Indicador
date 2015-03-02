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


class AcionamentoController extends AbstractActionController
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

        $ticketList = $this->getTables('Noc\Model\AcionamentoTicket')->ticketList($postData);
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

    public function volumeAcionamentoAction()
    {

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\AcionamentoTicket')->volumeAcionamento($postData);

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

    public function volumeAcioAfetaAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\AcionamentoTicket')->volumeAcioAfeta($postData);

        $valuesGraph = NULL;

        foreach ($volumeTicket as $data){
            $volumeAfeta = $data['volumeafeta'];
            $volumeNaoAfeta = $data['volumenaoafeta'];

            $valuesGraph .= "['Não afeta serviço',".$volumeNaoAfeta."],";
            $valuesGraph .= "['Afeta serviço',".$volumeAfeta."],";
        }

        $volumeTotal = $volumeAfeta + $volumeNaoAfeta;

        $viewModel = new ViewModel(array(
            'graph' => $valuesGraph ,
            'values' => $volumeTotal ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function volumeAcioAreaAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\AcionamentoTicket')->volumeAcioArea($postData);

        $valuesGraph = NULL;
        $constel = 0;
        $forjintel = 0;
        $httcuritiba = 0 ;
        $httosasco = 0;
        $httmaua = 0;
        foreach ($volumeTicket as $data) {

            ( $data['constel'] > 0 ) ? $constel = $constel + 1 : 'NULL';
            ( $data['forjintel'] > 0 ) ? $forjintel = $forjintel + 1 : 'NULL';
            ( $data['httcuritiba'] > 0 ) ? $httcuritiba = $httcuritiba + 1 : 'NULL';
            ( $data['httosasco'] > 0 ) ? $httosasco = $httosasco + 1 : 'NULL';
            ( $data['httmaua'] > 0 ) ? $httmaua = $httmaua + 1 : 'NULL';
            
        }

        $valuesGraph = "['constel',".$constel."],";
        $valuesGraph .= "['forjintel',".$forjintel."],";
        $valuesGraph .= "['httcuritiba',".$httcuritiba."],";
        $valuesGraph .= "['httosasco',".$httosasco."],";
        $valuesGraph .= "['httmaua',".$httmaua."],";

        $values = $constel + $forjintel + $httmaua + $httcuritiba + $httosasco;

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph ,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;
    }

    public function category($time){

        ($time > 0 && $time <= 14400 ) ?  $cat = 1 : '' ;
        ($time > 14400 && $time <= 28800 ) ? $cat = 2 : '';
        ($time > 28800 && $time <= 43200 ) ? $cat = 3 : '';
        ($time > 43200) ? $cat = 4 : '' ;

        return $cat;
    }
    
    public function volumeAcioAreaNAfetaAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\AcionamentoTicket')->volumeAcioAreaNAfeta($postData);
        $values = 0;

        for ($i = 1; $i <= 4; $i++ ){
            $constel[$i] = 0;
            $forjintel[$i] = 0;
            $httcuritiba[$i] = 0;
            $httosasco[$i] = 0;
            $httmaua[$i] = 0;
        }

        foreach ($volumeTicket as $data) {

            if( $data['constel'] > 0 ){
                $category = $this->category($data['constel']);
                ( !empty($constel[$category]) ) ? $constel[$category] = $constel[$category] + 1 : $constel[$category] = 1;
            }
            if( $data['forjintel'] > 0 ){
                $category = $this->category($data['forjintel']);
                ( !empty($forjintel[$category]) ) ? $forjintel[$category] = $forjintel[$category] + 1 : $forjintel[$category] = 1;
            }
            if( $data['httcuritiba'] > 0 ){
                $category = $this->category($data['httcuritiba']);
                ( !empty($httcuritiba[$category]) ) ? $httcuritiba[$category] = $httcuritiba[$category] + 1 : $httcuritiba[$category] = 1;
            }
            if( $data['httosasco'] > 0 ){
                $category = $this->category($data['httosasco']);
                ( !empty($httosasco[$category]) ) ? $httosasco[$category] = $httosasco[$category] + 1 : $httosasco[$category] = 1;
            }
            if( $data['httmaua'] > 0 ){
                $category = $this->category($data['httmaua']);
                ( !empty($httmaua[$category]) ) ? $httmaua[$category] = $httmaua[$category] + 1 : $httmaua[$category] = 1;
            }

            $values = 1;
        }

        for ($i = 1; $i <= 4; $i++) {

            $valuesGraph[$i] = '';
            $valuesGraph[$i] .= $constel[$i] . ",";
            $valuesGraph[$i] .= $forjintel[$i] . ",";
            $valuesGraph[$i] .= $httcuritiba[$i] . ",";
            $valuesGraph[$i] .= $httosasco[$i] . ",";
            $valuesGraph[$i] .= $httmaua[$i] . ",";
        }

        $legend = " 'constel', 'forjintel', 'httcuritiba', 'httosasco', 'httmaua' ";

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph,                
            'legend' => $legend,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }

    public function volumeAcioAreaAfetaAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Noc\Model\AcionamentoTicket')->volumeAcioAreaAfeta($postData);
        $values = 0;
        
        for ($i = 1; $i <= 4; $i++ ){
            $constel[$i] = 0;
            $forjintel[$i] = 0;
            $httcuritiba[$i] = 0;
            $httosasco[$i] = 0;
            $httmaua[$i] = 0;
        }

        foreach ($volumeTicket as $data) {

            if( $data['constel'] > 0 ){
                $category = $this->category($data['constel']);
                ( !empty($constel[$category]) ) ? $constel[$category] = $constel[$category] + 1 : $constel[$category] = 1;
            }
            if( $data['forjintel'] > 0 ){
                $category = $this->category($data['forjintel']);
                ( !empty($forjintel[$category]) ) ? $forjintel[$category] = $forjintel[$category] + 1 : $forjintel[$category] = 1;
            }
            if( $data['httcuritiba'] > 0 ){
                $category = $this->category($data['httcuritiba']);
                ( !empty($httcuritiba[$category]) ) ? $httcuritiba[$category] = $httcuritiba[$category] + 1 : $httcuritiba[$category] = 1;
            }
            if( $data['httosasco'] > 0 ){
                $category = $this->category($data['httosasco']);
                ( !empty($httosasco[$category]) ) ? $httosasco[$category] = $httosasco[$category] + 1 : $httosasco[$category] = 1;
            }
            if( $data['httmaua'] > 0 ){
                $category = $this->category($data['httmaua']);
                ( !empty($httmaua[$category]) ) ? $httmaua[$category] = $httmaua[$category] + 1 : $httmaua[$category] = 1;
            }

            $values = 1;
        }

        for ($i = 1; $i <= 4; $i++) {

            $valuesGraph[$i] = '';
            $valuesGraph[$i] .= $constel[$i] . ",";
            $valuesGraph[$i] .= $forjintel[$i] . ",";
            $valuesGraph[$i] .= $httcuritiba[$i] . ",";
            $valuesGraph[$i] .= $httosasco[$i] . ",";
            $valuesGraph[$i] .= $httmaua[$i] . ",";
        }

        $legend = " 'constel', 'forjintel', 'httcuritiba', 'httosasco', 'httmaua' ";

        $viewModel = new ViewModel(array(
            'valuesGraph' => $valuesGraph,                
            'legend' => $legend,
            'values' => $values ,
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }



      
}
