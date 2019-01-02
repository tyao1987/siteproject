<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Zend\Form\Form;

use Admin\Model\WfAppresource;
use Zend\View\Model\ViewModel;
use Admin\Model\WfSiteAppresource;
use Admin\Model\WfSite;


class AppresourceController extends AbstractController {

    protected $_controller = 'appresource';
    protected $_tableObj = null;
    public function __construct(){
        $this->redirectAdminUrl();
        $this->_tableObj = new WfAppresource();
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
    
    public function indexAction() {
        if ($this->request->isPost()) {
            $id = (int) $_POST['id'];
            $form = $this->_tableObj->getForm($_POST);
            if($form->isValid()){
                 $data = $form->getData();
                 if($id == 0){
                     $this->_tableObj->insertRow($data);
                     return $this->redirect()->toRoute('default', array(
                         'controller'=> $this->_controller,
                         'action'    => 'list'
                     ));
                 }else{
                     $row = $this->_tableObj->getRowById($id);
                     if($row && $row['project_id'] == $this->_user['project_id']){
                         $this->_tableObj->updateRowById($data,$id);
                     }
                     return $this->redirect()->toRoute('default', array(
                         'controller'=> $this->_controller,
                         'action'    => 'list'
                     ));
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
        
        if($id){
        	$siteAppReasource = new WfSiteAppresource();
        	$viewData['siteAppList'] = $siteAppReasource->getList(array('app_resource_id' => $id),array('id ASC'));
        	$site = new WfSite();
        	$viewData['siteList'] = $site->getList(array('is_delete' => 0));
        }
        
        $viewData['error'] = $form->getMessages();
        
        return new ViewModel($viewData);
        
    }
    
    public function addSiteAction() {
    	
    	$result = array('siteName' => '','id'=>0);
    	$siteId = (int)$this->params()->fromPost("siteId", 0);
    	$appId = (int)$this->params()->fromPost("appId", 0);
    	$siteAppresource = new WfSiteAppresource();
    	$row = $siteAppresource->fetchRow(array('site_id' => $siteId,'app_resource_id' => $appId));
    	if(!$row){
    		$data = array();
    		$data['site_id'] = $siteId;
    		$data['app_resource_id'] = $appId;
    		$id = $siteAppresource->insertRow($data);
    		$result['id'] = $id;
    		$site = new WfSite();
    		$siteRow = $site->fetchRow(array('id'=>$siteId));
    		$result['siteName'] = $siteRow['name'];
    	}
    	
    	echo json_encode(array('data' => $result));exit;
    }
    
    public function removeSiteAction() {
    	
    	$siteAppId = (int)$this->params()->fromPost("siteAppId", 0);
    	$siteAppresource = new WfSiteAppresource();
    	$siteAppresource->deleteById($siteAppId);
    	echo json_encode("success");exit;
    }
    
}