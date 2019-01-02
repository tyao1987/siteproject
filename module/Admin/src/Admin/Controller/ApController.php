<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Admin\Model\Auth;
use Zend\Form\Form;

use Zend\View\Model\ViewModel;
use Admin\Model\WfAp;


class ApController extends AbstractController {

    
    protected $_controller = 'ap';
    protected $_tableObj = null;
    public function __construct(){
        $this->redirectAdminUrl();
        $this->_tableObj = new WfAp();
    }
    
    public function listAction(){
        $param = $this->params()->fromQuery();
        if(!isset($param['is_delete'])){
            $param['is_delete'] = 0;
        }
        $paginator = $this->_tableObj->paginator($param);
        $paginator->setCurrentPageNumber ( ( int ) $param ['page'] );
        if(empty($param['perpage'])){
            $param['perpage'] = 20;
        }
        $paginator->setItemCountPerPage ( $param['perpage'] );
        
        
        $viewData ['paginator'] = $paginator;
        $viewData ['param'] = $param;
        $viewData = array_merge ($viewData, $param);
        
        return new ViewModel ($viewData);
        
    }
    
    public function checkAc($data){
        $return  = true;
        $first = $data['first_ac_id'];
        $second = $data['second_ac_id'];
        $third = $data['third_ac_id'];
        if($second != '' || $third != ''){
            if($first == $second || $second == $third || $first == $third){
                $return  = false;
            }
        }
        if(!$return){
            return "主AC,从AC,第三AC重复";
        }
        return '';
    }
    
    public function indexAction() {
        if ($this->request->isPost()) {
            $id = (int) $_POST['id'];
            $form = $this->_tableObj->getForm($_POST);
            if($form->isValid()){
                 $data = $form->getData();
                 if($id == 0){
                     $errorMsg = $this->checkAc($data);
                     if(!$errorMsg){
                         $this->_tableObj->insertRow($data);
                         return $this->redirect()->toRoute('default', array(
                             'controller'=> $this->_controller,
                             'action'    => 'list'
                         ));
                     }
                 }else{
                     $row = $this->_tableObj->getRowById($id);
                     if($row && $row['project_id'] == $this->_user['project_id']){
                         $errorMsg = $this->checkAc($data);
                         if(!$errorMsg){
                             $this->_tableObj->updateRowById($data,$id);
                             return $this->redirect()->toRoute('default', array(
                                 'controller'=> $this->_controller,
                                 'action'    => 'list'
                             ));
                         }
                     }else{
                         return $this->redirect()->toRoute('default', array(
                             'controller'=> $this->_controller,
                             'action'    => 'list'
                         ));
                     }
                 }
            }
        }else{
            $id = (int)$this->params()->fromRoute("id", 0);
            if($id > 0){
                $row = $this->_tableObj->getRowById($id);
                if($row && $row['project_id'] == $this->_user['project_id']){
                    $data = $this->objToArray($row);
                    $form = $this->_tableObj->getForm($data);
                }else{
                    return $this->redirect()->toRoute('default', array(
                        'controller'=> $this->_controller,
                        'action'    => 'list'
                    ));
                }
            }else{
                $form = $this->_tableObj->getForm();
            }
        }
        
        $viewData = array ();
        $viewData['form'] = $form;
        $viewData['error'] = $form->getMessages();
        if($errorMsg){
            $viewData['error']['save'] = $errorMsg;
        }
        
        return new ViewModel($viewData);
        
    }
    
    
}