<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Zend\Form\Form;

use Admin\Model\WfSite;
use Admin\Model\WfSiteTag;
use Admin\Model\WfTag;
use Zend\View\Model\ViewModel;
use Admin\Model\WfProject;


class SitesController extends AbstractController {

    protected $_listPath = null;
    protected $_controller = 'sites';
    protected $_tableObj = null;
    public function __construct(){
        $this->redirectAdminUrl();
        $this->_tableObj = new WfSite();
    }
    
    public function checkImage(){
        
        $data = Data::getInstance();
        $config = $data->get('config');
        $this->_listPath = $config['siteImagePath'][$this->_user['project_id']];
        $result = array();
        $result['error'] = '';
        if (!is_dir($this->_listPath)) {
            @mkdir ($this->_listPath, 0755, true );
        }
        $pathinfo = pathinfo ( $_FILES ['file']['name'] );
        //$fileName = $pathinfo ['filename'];
        $ext = strtolower ( $pathinfo ['extension'] );
        $fileNameWithExt = uniqid() . '.' . $ext;
        move_uploaded_file ( $_FILES ['file'] ['tmp_name'], $this->_listPath .'/'. $fileNameWithExt );
        if(file_exists($this->_listPath .'/'. $fileNameWithExt)){
            $imageServer = $config['siteImageServer'][$this->_user['project_id']];
            if(substr($imageServer, -1) == '/'){
                $imageServer = substr($imageServer, 0,-1);
            }
            $result['path'] = $imageServer . '/'. $fileNameWithExt;
        }else{
            $result['error'] = '上传图片失败';
        }
        return $result;
        
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
        $errorMsg = "";
        if ($this->request->isPost()) {
            $id = (int) $_POST['id'];
            $form = $this->_tableObj->getForm($_POST);
            if($form->isValid()){
                 $data = $form->getData();
                 if($id == 0){
                     if($_FILES['file']['name'] != ''){
                         $result = $this->checkImage();
                         if($result['error'] == ''){
                             $data['image_url'] = $result['path'];
                         }else{
                             $errorMsg = $result['error'];
                         }
                     }
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
                         if($_FILES['file']['name'] != ''){
                             $result = $this->checkImage();
                             if($result['error'] == ''){
                                 $data['image_url'] = $result['path'];
                             }else{
                                 $errorMsg = $result['error'];
                             }
                         }
                         if(!$errorMsg){
                             $this->_tableObj->updateRowById($data,$id);
                         }
                     }
                 }
                 
                 if(!$errorMsg){
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
            $siteTag = new WfSiteTag();
            $viewData['siteTagList'] = $siteTag->getList(array('site_id' => $id),array('id ASC'));
            $tag = new WfTag();
            $viewData['tagList'] = $tag->getList(array('is_delete' => 0));
        }
        $viewData['error'] = $form->getMessages();
        if($errorMsg){
            $viewData['error']['save'] = $errorMsg;
        }
        
        return new ViewModel($viewData);
        
    }
    
    public function addTagAction() {
        
        $result = array('tagName' => '','id'=>0);
        $siteId = (int)$this->params()->fromPost("siteId", 0);
        $tagId = (int)$this->params()->fromPost("tagId", 0);
        $siteTag = new WfSiteTag();
        $row = $siteTag->fetchRow(array('site_id' => $siteId,'tag_id' => $tagId));
        if(!$row){
            $data = array();
            $data['site_id'] = $siteId;
            $data['tag_id'] = $tagId;
            $id = $siteTag->insertRow($data);
            $result['id'] = $id;
            $tag = new WfTag();
            $tagRow = $tag->fetchRow(array('id'=>$tagId));
            $result['tagName'] = $tagRow['name'];
        }
        
        echo json_encode(array('data' => $result));exit;
    }
    
    public function removeTagAction() {
        
        $siteTagId = (int)$this->params()->fromPost("siteTagId", 0);
        $siteTag = new WfSiteTag();
        $siteTag->deleteById($siteTagId);
        echo json_encode("success");exit;
    }
    
    public function exportAction(){
        $crs = array();
        $crs['type'] = "name";
        $crs['properties']['name'] = "urn:ogc:def:crs:OGC:1.3:CRS84";
        $return['type'] = "FeatureCollection";
        $return['crs'] = $crs;
        $return['features'] = array();
        $data = Data::getInstance();
        $config = $data->get('config');
        $ssid = $config['siteSSID'][$this->_user['project_id']];
        $list = $this->_tableObj->getList(array('is_delete' => 0,'gps_e > ?' => 0,'gps_n > ? ' => 0,'project_id' => $this->_user['project_id']),array('id ASC'));
        if($list){
            foreach ($list as $key => $l){
                $row = array();
                $row['type'] = "Feature";
                $row['properties'] = array();
                $row['properties']['Id'] = $key + 1;
                $row['properties']['Name'] = $l['name'] ? $l['name'] : '';
                $row['properties']['Address'] = $l['address'] ? $l['address'] : '';
                $row['properties']['Ssid'] = $ssid;
                $row['properties']['Image'] = $l['image_url'] ? $l['image_url'] : '';
                $row['properties']['Description'] = $l['description'] ? $l['description'] : '';
                $row['geometry'] = array();
                $row['geometry']['type'] = 'Point';
                $row['geometry']['coordinates'] = array();
                //经纬度默认重庆市政府
                //106.556901,29.570045
                $row['geometry']['coordinates'][] = floatval($l['gps_e']);
                $row['geometry']['coordinates'][] = floatval($l['gps_n']);
                $return['features'][] = $row;
            }
        }
        $projectId = $this->_user['project_id'];
        $project = new WfProject();
        $row = $project->getRowById($projectId);
        $jsonFile = ROOT_PATH . '/public/import/'.$row['file_name'].'/wifi.json';
        unlink($jsonFile);
        $return = json_encode($return);
        file_put_contents($jsonFile, $return);
        $fileName = ROOT_PATH.'/public/wifi.json';
        $file = fopen ($fileName,"r" );
        //输入文件标签
        header ( "Content-type: application/octet-stream" );
        header ( "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0"); // HTTP/1.1
        header ( "Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header ( "Pragma: no-cache"); // Date in the past
        header ( "Accept-Ranges: bytes" );
        header ( "Accept-Length: " . filesize ($fileName) );
        header ( "Content-Disposition: attachment; filename=" . 'wifi.json' );
        //输出文件内容
        //读取文件内容并直接输出到浏览器
        echo fread ($file, filesize ($fileName));
        fclose ($file);
        exit;
    }
}