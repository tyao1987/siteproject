<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Zend\Form\Form;

use Admin\Model\WfBas;
use Zend\View\Model\ViewModel;
use Admin\Util\Util;

class BasController extends AbstractController {

    protected $_controller = 'bas';
    protected $_tableObj = null;
    public function __construct(){
        $this->redirectAdminUrl();
        $this->_tableObj = new WfBas();
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
    
    public function checkIpPool($data){
        $ipPool = trim($data['ip_pool']);
        if($ipPool){
            $ipPoolArray = explode(",", $ipPool);
            foreach ($ipPoolArray as $ipPoolValue){
                $v = explode('/', $ipPoolValue);
                if(count($v) != 2){
                    return "ip_pool 格式不正确";
                }else{
                    $firstIp = $v[0];
                    $ipCount = $v[1];
                    if(!preg_match(Util::$firstIpRegex, $firstIp) || !preg_match(Util::$ipCountRegex, $ipCount)){
                        return "ip_pool 格式不正确";
                    }
                }
            }
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
                     $errorMsg = $this->checkIpPool($data);
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
                         $errorMsg = $this->checkIpPool($data);
                         if(!$errorMsg){
                             $this->_tableObj->updateRowById($data,$id);
                             return $this->redirect()->toRoute('default', array(
                                 'controller'=> $this->_controller,
                                 'action'    => 'list'
                             ));
                         }
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