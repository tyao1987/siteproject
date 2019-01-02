<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Admin\Model\Auth;
use Zend\Form\Form;

use Zend\View\Model\ViewModel;
use Admin\Model\WfProject;
use Admin\Model\User;


class ProjectController extends AbstractController {

    protected $_controller = 'project';
    protected $_tableObj = null;
    public function __construct(){
        $this->_tableObj = new WfProject();
    }
    
    public function listAction(){
        $param = $this->params()->fromQuery();
        $paginator = $this->_tableObj->paginator($param);
        $paginator->setCurrentPageNumber ( ( int ) $param ['page'] );
        if(empty($param['perpage'])){
            $param['perpage'] = 20;
        }
        $paginator->setItemCountPerPage ( $param['perpage'] );
        
        
        $viewData ['paginator'] = $paginator;
        $viewData ['param'] = $param;
        if($param['error'] == 'exist_user'){
            $viewData['error']['delete'] = '已存在关联用户 不可删除';
            unset($param['error']);
        } 
        
        $viewData = array_merge ($viewData, $param);
        
        return new ViewModel ($viewData);
        
    }
    
    public function indexAction() {
        if ($this->request->isPost()) {
            $form = $this->_tableObj->getForm($_POST);
            if($form->isValid()){
                 $data = $form->getData();
                 $authIdentity = Auth::getIdentity();
                 $data['user_id'] = $authIdentity['id'];
                 $this->_tableObj->insertRow($data);
                 return $this->redirect()->toRoute('default', array(
                     'controller'=> $this->_controller,
                     'action'    => 'list'
                 ));
            }
        }else{
            $form = $this->_tableObj->getForm();
        }
        
        $viewData = array ();
        $viewData['form'] = $form;
        $viewData['error'] = $form->getMessages();
        
        return new ViewModel($viewData);
        
    }
    
    public function deleteAction() {
        $identity = Auth::getIdentity();
        $userId = $identity['id'];
        if($userId == 1){
           $id = (int)$this->params()->fromRoute("id", 0);
           $user = new User();
           $where = array();
           $where['project_id'] = $id;
           $list = $user->getList($where);
           if(!$list){
               $this->_tableObj->tableGateway->delete(array('id' => $id));
               $url = '/project/list';
           }else{
               $url = '/project/list?error=exist_user';
           }
           return $this->_redirect($url);
        }
    }
}