<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Test\Data;

use Admin\Model\Auth;
use Zend\Form\Form;

use Admin\Model\WfImport;
use Zend\View\Model\ViewModel;
use Admin\Model\WfProject;

class ImportController extends AbstractController {

    public function __construct(){
        $this->redirectAdminUrl();
    }
    
    protected $_listPath = null;
    
    //protected $_sheetList = array('Tag','Audit','ACCLUSTER','APGROUP','AC','SITE','AP','PORTAL','Bas');
    
    protected $_sheetList = array('Tag','Audit','ACCLUSTER','APGROUP','AC','SITE','AP','Bas');
    
    public function checkExcel($name,$update = false){
        $projectId = $this->_user['project_id'];
        $project = new WfProject();
        $row = $project->getRowById($projectId);
        $this->_listPath = ROOT_PATH . '/public/import/'.$row['file_name'];
        $result = array();
        $result['error'] = '';
        if (!is_dir($this->_listPath)) {
            @mkdir ($this->_listPath, 0755, true );
        }
        
        $pathinfo = pathinfo ( $_FILES ['file']['name'] );
        //$fileName = $pathinfo ['filename'];
        $ext = strtolower ( $pathinfo ['extension'] );
        
        //$fileNameWithExt = uniqid() . '.' . $ext;
        $allowExt = array("xls","xlsx");
        if ($_FILES ['file'] ['error'] > 0) {
            $result['error'] = "{$name} 上传错误. 错误原因 : " . $this->getErr ( $_FILES ['file'] ['error'] );
            return $result;
        }
        
        if (! in_array ( $ext, $allowExt )) {
            $result['error'] = $ext . " 文件类型 {$name} 错误";
            return $result;
        }
        
        
        if (! is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
            $result['error'] = "文件上次错误 文件名称" . $_FILES ['file'] ['tmp_name'];
            return $result;
        }
        if($update){
            $name = str_replace(".".$ext, "", $name).'('.date('Y-m-d-h-i-s').").".strtolower($pathinfo['extension']);
        }
        move_uploaded_file ( $_FILES ['file'] ['tmp_name'], $this->_listPath .'/'.$name );
        if(file_exists($this->_listPath .'/'. $name)){
            $result['path'] = $this->_listPath .'/'. $name;
            $result['name'] = $name;
        }else{
            $result['error'] = '上传失败';
        }
        return $result;
        
    }
    
    public function listAction(){
        $param = $this->params()->fromQuery();
        $import = new WfImport();
        $paginator = $import->paginator($param);
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
    
    public function checkExcelData($file){
        require_once ROOT_PATH . '/vendor/PHPExcel/PHPExcel.php';
        $phpExcel = new \PHPExcel();
        $result = array();
        $result['error'] = array();
        $result['data'] = array();
        $objReader = \PHPExcel_IOFactory::createReaderForFile($file);
        $objPHPExcel = $objReader->load($file,$encode='utf-8');
        foreach ($this->_sheetList as $sheetName){
            $sheetData = array();
            $sheetObj = $objPHPExcel->getSheetByName($sheetName);
            $sheetData = $sheetObj->toArray();
            $result['data'][$sheetName] = $sheetData;
            if(count($sheetData) > 2){
                $class = 'Admin\\Model\\Wf'.ucfirst(strtolower($sheetName));
                $obj = new $class();
                $checkResult = $obj->checkImport($sheetData,$sheetName);
                if($checkResult['error']){
                    foreach ($checkResult['error'] as $error){
                        $result['error'][] = $error;
                    }
                }
            }
        }
        return $result;
    }
    
    public function initDataAction(){
    	$import = new WfImport();
    	var_dump($import->initData());
    	exit;
    }
    
//     public function testAction(){
//         require_once ROOT_PATH . '/vendor/PHPExcel/PHPExcel.php';
//         $phpExcel = new \PHPExcel();
//         $result = array();
//         $file = ROOT_PATH .'/public/import/test.xlsx';
//         $objReader = \PHPExcel_IOFactory::createReaderForFile($file);
//         $objPHPExcel = $objReader->load($file,$encode='utf-8');
//         foreach ($this->_sheetList as $sheetName){
//             $sheetObj = $objPHPExcel->getSheetByName($sheetName);
//             $sheetData = $sheetObj->toArray();
//             if(count($sheetData) > 2){
//                 $class = 'Admin\\Model\\Wf'.ucfirst(strtolower($sheetName));
//                 $obj = new $class();
//                 $checkResult = $obj->checkImport($sheetData,$sheetName);
//                 if($checkResult['error']){
//                     foreach ($checkResult['error'] as $error){
//                         $result['error'][] = $error;
//                     }
//                 }
//             }
//         }
//         return $result;
//     }
    
    
    public function indexAction() {
        $import = new WfImport();
        $errorMsg = "";
        $imagePath = '';
        if ($this->request->isPost()) {
            $form = $import->getForm($_POST);
            if($form->isValid()){
                $data = $form->getData();
                $authIdentity = Auth::getIdentity();
                $data['name'] = trim($data['name']);
                $row = $import->getImportByName($data['name']);
                //判断上传文件是否已经保存
                if(!$row){
                    $result = $this->checkExcel($data['name']);
                }else{
                    $result = $this->checkExcel($data['name'],true);
                    $data['name'] = $result['name'];
                }
                if($result['error'] == ''){
                    //检查excel数据
                    $path = $result['path'];
                    $result = $this->checkExcelData($result['path']);
                    if(!$result['error']){
                        $result = $import->insertData($result['data']);
                        if(!$result['error']){
                            //if(!$row){
                                $import->insertImport($data);
                            //}else{
                                //unset($data['user_id']);
                                //$import->updateImport($row->id, $data);
                                //@unlink($this->_listPath . '/'.$row['name']);
                                //@rename($path, $this->_listPath . '/'.$row['name']);
                                //@unlink($path);
                            //}
                            return $this->redirect()->toRoute('default', array(
                                'controller'=> 'import',
                                'action'    => 'list'
                            ));
                        }else{
                            @unlink($path);
                            foreach ($result['error'] as $error){
                                $errorMsg .= $error."<br>";
                            }
                        }
                        
                    }else{
                        @unlink($path);
                        foreach ($result['error'] as $error){
                            $errorMsg .= $error."<br>";
                        }
                    }
                }else{
                    $errorMsg = $result['error'];
                }
            }
        }else{
            $id = (int)$this->params()->fromRoute("id", 0);
            if($id > 0){
                $row = $import->getImportById($id);
                if($row){
                    $data = $this->objToArray($row);
                    $form = $import->getForm($data);
                    
                }else{
                    return $this->redirect()->toRoute('default', array(
                        'controller'=> 'auth',
                        'action'    => 'no-auth',
                    ));
                }
            }else{
                $form = $import->getForm();
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