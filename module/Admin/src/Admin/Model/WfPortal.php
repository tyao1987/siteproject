<?php
namespace Admin\Model;


use Application\Model\DbTable;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;

use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Validator\Db\NoRecordExists;
use Admin\Util\Util;
use Zend\Db\Sql\Where;

class WfPortal extends DbTable
{
    protected $_defaultNullFilter = array();
	protected $_name = 'wf_portal';
	protected $_uniqueColumn = array('name');
	protected $_projectId = 0;
	protected $_userId = 0;
	
	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
		$identity = Auth::getIdentity();
		$this->_projectId = (int)$identity['project_id'];
		$this->_userId = (int)$identity['id'];
	}

	
	public function paginator($conditions = array()) {
	    unset($conditions['page']);
	    unset($conditions['perpage']);
	    $dbAdapter = $this->tableGateway->getAdapter();
	    $sql = new Sql ( $dbAdapter );
	    if ($conditions) {
	        foreach ($conditions as $key => $val) {
	            $this->_select->where($this->quoteInto("{$key} like ?", '%' .$val. '%'));
	        }
	    }
	    $this->_select->where(array('project_id' => $this->_projectId));
	    $this->_select->order(array('id ASC'));
	    
	    $adapter = new DbSelect ($this->_select, $sql);
	    $paginator = new Paginator ( $adapter );
	    
	    return $paginator;
	    
	}
	
	public function insertRow($data){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $this->insert($data);
	    return $this->tableGateway->lastInsertValue;
	}
	
	
	public function getForm($data = array()){
	    $form = new Form();
	    if($data['id']){
	        $id = (int)$data['id'];
	    }else{
	        $id = 0;
	    }
	    $form->setAttribute('class', 'form-horizontal')
	    ->setAttribute('method', 'post')
	    ->setAttribute('id', 'portal_form')
	    ->setAttribute('enctype', 'multipart/form-data');
	    $controller = "portal";
	    $action = "/".$controller."/index";
	    $cannelUrl = "/".$controller."/list";
	    if($id){
	        $action .= "/id/".$id;
	        $form->setAttribute('action', $action);
	    }else{
	        $form->setAttribute('action', $action);
	    }
	    
	    $form->add(array(
	        'name' => 'name',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'name',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    $form->add(array(
	        'name' => 'portal_id',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'portal_id',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'url',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'url',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    
	    $form->add(array(
	        'name' => 'description',
	        'type' => 'Textarea',
	        'attributes' => array(
	            'id'    => 'description',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'submit',
	        'type' => 'Submit',
	        'attributes' => array(
	            'value' => '提交',
	            'class' => 'btn btn-primary btn-lg',
	            'style'=>'width: 20%',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'cancel',
	        'type' => 'Button',
	        'options' => array(
	            'label' => '取消',
	            
	        ),
	        'attributes' => array(
	            'value' => 'Cancel',
	            'class' => 'btn btn-primary btn-lg',
	            'style'=>'width: 20%',
	            'onclick' => "window.location='$cannelUrl';",
	        ),
	    ));
	    
	    $form->add(
	        array(
	            'name' => 'user_id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $this->_userId,
	                'id'    => 'user_id',
	            ),
	        ));
	    
	    $form->add(
	        array(
	            'name' => 'project_id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $this->_projectId,
	                'id'    => 'project_id',
	            ),
	        ));
	    
	    $form->add(
	        array(
	            'name' => 'id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $id,
	                'id'    => 'id',
	            ),
	        ));
	    $inputFilter = new InputFilter();
	    $factory     = new Factory();
	    if(!$id){
	        $where = new Where();
	        $where->equalTo('portal_id', null);
	        $where->equalTo('project_id', $this->_projectId);
	        $inputFilter->add(
	            $factory->createInput(
	                array(
	                    'name'     => 'portal_id',
	                    'required' => true,
	                    'filters'  => array(
	                        array('name' => 'StripTags'),
	                        array('name' => 'StringTrim'),
	                    ),
	                    'validators' => array(
	                        array(
	                            'name'    => 'Db\NoRecordExists',
	                            'options' => array(
	                                'table' => $this->_name,
	                                'field' => 'portal_id',
	                                'adapter' => $this->tableGateway->getAdapter(),
	                                'message' => 'portal的id 已存在',
	                                'exclude' => $where,
	                            ),
	                        ),
	                    ),
	                )
	                )
	            );
	        $where = new Where();
	        $where->equalTo('name', null);
	        $where->equalTo('project_id', $this->_projectId);
	        $inputFilter->add(
	            $factory->createInput(
	                array(
	                    'name'     => 'name',
	                    'required' => true,
	                    'filters'  => array(
	                        array('name' => 'StripTags'),
	                        array('name' => 'StringTrim'),
	                    ),
	                    'validators' => array(
	                        array(
	                            'name'    => 'Db\NoRecordExists',
	                            'options' => array(
	                                'table' => $this->_name,
	                                'field' => 'name',
	                                'adapter' => $this->tableGateway->getAdapter(),
	                                'message' => 'portal名称 已存在',
	                                'exclude' => $where,
	                            ),
	                        ),
	                    ),
	                )
	                )
	            );
	        
	        
	        
	    }else{
	        $where = new Where();
	        $where->equalTo('portal_id', null);
	        $where->equalTo('project_id', $this->_projectId);
	        $where->notEqualTo('id', $id);
	        $inputFilter->add(
	            $factory->createInput(
	                array(
	                    'name'     => 'portal_id',
	                    'required' => true,
	                    'filters'  => array(
	                        array('name' => 'StripTags'),
	                        array('name' => 'StringTrim'),
	                    ),
	                    'validators' => array(
	                        array(
	                            'name'    => 'Db\NoRecordExists',
	                            'options' => array(
	                                'table' => $this->_name,
	                                'field' => 'portal_id',
	                                'adapter' => $this->tableGateway->getAdapter(),
	                                'message' => 'portal的id 已存在',
	                                'exclude' => $where,
	                            ),
	                            
	                        ),
	                    ),
	                )
	                )
	            );
	        
	        $where = new Where();
	        $where->equalTo('name', null);
	        $where->equalTo('project_id', $this->_projectId);
	        $where->notEqualTo('id', $id);
	        $inputFilter->add(
	            $factory->createInput(
	                array(
	                    'name'     => 'name',
	                    'required' => true,
	                    'filters'  => array(
	                        array('name' => 'StripTags'),
	                        array('name' => 'StringTrim'),
	                    ),
	                    'validators' => array(
	                        array(
	                            'name'    => 'Db\NoRecordExists',
	                            'options' => array(
	                                'table' => $this->_name,
	                                'field' => 'name',
	                                'adapter' => $this->tableGateway->getAdapter(),
	                                'message' => 'portal名称 已存在',
	                                'exclude' => $where,
	                            ),
	                        ),
	                    ),
	                )
	                )
	            );
	        
	        
	    }
	    
	    $form->setInputFilter($inputFilter);
	    //set data
	    if (is_array ($data)) {
	        $form->setData($data);
	    }
	    return $form;
	}
	
	
	public function updateRowById($data,$id){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where = array();
	    $where[] = $this->quoteInto('id = ?', $id);
	    return $this->tableGateway->update($data, $where);
	}
	
	public function updateRowByPortalId($data,$portalId){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where = array();
	    $where[] = $this->quoteInto('portal_id = ?', $portalId);
	    $where[] = $this->quoteInto('project_id = ?', $this->_projectId);
	    return $this->tableGateway->update($data, $where);
	}
	
	public function getRowById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
	}
	
	public function _checkExcelData($sheetData,$sheetName){
	    $dataCheck = array();
	    $result = Util::checkImportUnique($sheetData, array('portalid','portalname'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportRequired($sheetData, array('portalid','portalname'), $sheetName);
	    Util::getError($dataCheck, $result);
	    return $dataCheck;
	}
	
	public function checkImport($sheetData,$sheetName){
	    $dataCheck = $this->_checkExcelData($sheetData, $sheetName);
	    if($dataCheck['error']){
	        return $dataCheck;
	    }
	    $result = array();
	    $titleColumn = array_flip($sheetData[0]);
	    foreach ($sheetData as $key => $value){
	        if($key == 0 || $key == 1){
	            continue;
	        }
	        $errorRow = $sheetName . ' 数据错误 第'. ($key + 1)."行 ";
	        $option = Util::getOption($value[$titleColumn['update']]);
	        //插入2，有更新1，没更新0
	        if(in_array($option, array(0,1,2))){
	            $name = trim($value[$titleColumn['portalname']]);
	            $portalId = trim($value[$titleColumn['portalid']]);
	            if($option != 0){
	                if($option == 1){
	                    $currentRow = $this->fetchRow(Util::getWhereArr(array('portal_id' => $portalId)));
	                    if(!$currentRow){
	                       $result['error'][] = $errorRow."portalid 不存在";
	                    }else{
	                        //if($name != $currentRow['name']){
	                            //$checkArray = array();
	                            //$checkArray['tag_name'] = array('name' => $name);
	                            //Util::checkRowExist($checkArray,$errorRow,$result,$this);
	                        //}
	                    }
	                }
	                if($option == 2){
	                    $checkArray = array();
	                    $checkArray['portalid'] = array('portal_id' => $portalId);
	                    $checkArray['portalname'] = array('name' => $name);
	                    Util::checkRowExist($checkArray,$errorRow,$result,$this);
	                }
	            }
	        }
	    }
	    return $result;
	}

	public function insertData($sheetData,$sheetName){
	    $result = array();
	    $result['error'] = array();
	    $titleColumn = array_flip($sheetData[0]);
	    foreach ($sheetData as $key => $value){
	        if($key == 0 || $key == 1){
	            continue;
	        }
	        $option = Util::getOption($value[$titleColumn['update']]);
 	        //插入2，有更新1，没更新0
	        if(in_array($option, array(1,2))){
	            $data = array();
	            $name = trim($value[$titleColumn['portalname']]);
	            $portalId = trim($value[$titleColumn['portalid']]);
	            $data['url'] = trim($value[$titleColumn['url']]);
	            $data['description'] = trim($value[$titleColumn['description']]);
	            $data['portal_id'] = $portalId;
	            $data['name'] = $name;
	            $data['user_id'] = $this->_userId;
	            $data['project_id'] = $this->_projectId;
                if($option == 1){
                    unset($data['user_id']);
                    unset($data['portal_id']);
                    $this->updateRowByPortalId($data,$portalId);
                }
                if($option == 2){
                    $this->insertRow($data);
                }
	        }
	    }
	    $checkResult = Util::checkUniqueColumn($this->_uniqueColumn,$this,$this->_name);
	    Util::getError($result, $checkResult);
	    return $result;
	}
	
	
	public function getList($where){
	    $data = $this->tableGateway->getAdapter();
	    $select = new Select();
	    $select->from($this->_name);
	    $where['project_id'] = $this->_projectId;
	    $this->_select->where($where);
	    $sql = $this->_select->getSqlString($data->getPlatform());
	    return $this->fetchAll($sql);
	}

	public function checkInitData(){
		$result = array();
		$result['error'] = array();
		$checkResult = Util::checkUniqueColumn($this->_uniqueColumn,$this,$this->_name);
		Util::getError($result, $checkResult);
		return $result;
	}
	
}