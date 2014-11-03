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

    public function totalTimeTypeAction(){

        $request = $this->getRequest();
        $postData = $request->getPost()->toArray();

        $volumeTicket = $this->getTables('Crm\Model\TmaTicket')->totalTimeType($postData);

        $arrayOne['Defeito'] = 0;
        $arrayOne['Solicitação'] = 0;
        $arrayOne['Reclamação'] = 0;
        $arrayOne['Informação'] = 0;
        $arrayOne['Alteração'] = 0;
        $arrayOne['Desconexão'] = 0;
        $arrayOne['Cobrança'] = 0;

        $arrayTwo['Defeito']  = 0;
        $arrayTwo['Solicitação'] = 0;
        $arrayTwo['Reclamação'] = 0;
        $arrayTwo['Informação'] = 0;
        $arrayTwo['Alteração'] = 0;
        $arrayTwo['Desconexão'] = 0;
        $arrayTwo['Cobrança'] = 0;

        $arrayThree['Defeito'] = 0;
        $arrayThree['Solicitação'] = 0;
        $arrayThree['Reclamação'] = 0;
        $arrayThree['Informação'] = 0;
        $arrayThree['Alteração'] = 0;
        $arrayThree['Desconexão'] = 0;
        $arrayThree['Cobrança'] = 0;

        $arrayFour['Defeito'] = 0;
        $arrayFour['Solicitação'] = 0;
        $arrayFour['Reclamação'] = 0;
        $arrayFour['Informação'] = 0;
        $arrayFour['Alteração'] = 0;
        $arrayFour['Desconexão'] = 0;
        $arrayFour['Cobrança'] = 0;

        foreach ($volumeTicket as $data) {
            
            if( $data['tmatotal'] == 'Até 4 horas' || $data['tmatotal'] == 'Fechado na hora da abertura' ){
                $arrayOne[$data['type']] = $data['total'];
            }
            else if( $data['tmatotal'] == 'De 4 a 8 horas'){
                $arrayTwo[$data['type']] = $data['total'];
            }
            else if( $data['tmatotal'] == 'De 8 a 12 horas'){
                $arrayThree[$data['type']] = $data['total'];
            }
            else if( $data['tmatotal'] == 'Acima de 12 horas'){
                $arrayFour[$data['type']] = $data['total'];
            }
        }

        $valuesOne = $arrayOne['Defeito'].",".$arrayOne['Solicitação'].",".$arrayOne['Reclamação'].",".$arrayOne['Informação'].",".$arrayOne['Alteração'].",".$arrayOne['Desconexão'].",".$arrayOne['Cobrança'];
        $valuesTwo = $arrayTwo['Defeito'].",".$arrayTwo['Solicitação'].",".$arrayTwo['Reclamação'].",".$arrayTwo['Informação'].",".$arrayTwo['Alteração'].",".$arrayTwo['Desconexão'].",".$arrayTwo['Cobrança'];
        $valuesThree = $arrayThree['Defeito'].",".$arrayThree['Solicitação'].",".$arrayThree['Reclamação'].",".$arrayThree['Informação'].",".$arrayThree['Alteração'].",".$arrayThree['Desconexão'].",".$arrayThree['Cobrança'];
        $valuesFour = $arrayFour['Defeito'].",".$arrayFour['Solicitação'].",".$arrayFour['Reclamação'].",".$arrayFour['Informação'].",".$arrayFour['Alteração'].",".$arrayFour['Desconexão'].",".$arrayFour['Cobrança'];
        
        $viewModel = new ViewModel(array(
            'valuesOne' => $valuesOne ,
            'valuesTwo' => $valuesTwo ,
            'valuesThree' => $valuesThree ,
            'valuesFour' => $valuesFour 
        ));

        $viewModel->setTerminal(true);

        return $viewModel;

    }
    
}
