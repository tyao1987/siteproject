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

class WfBas extends DbTable
{
    protected $_defaultNullFilter = array();
	protected $_name = 'wf_bas';
	protected $_uniqueColumn = array('name','nas_ip');
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
	    $dbAdapter = $this->tableGateway->getAdapter ();
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
                ->setAttribute('id', 'bas_form')
                ->setAttribute('enctype', 'multipart/form-data');
        $controller = "bas";
        $action = "/".$controller."/index";
        $cannelUrl = "/".$controller."/list";
        if($id){
            $action .= "/id/".$id;
            $form->setAttribute('action', $action);
        }else{
            $form->setAttribute('action', $action);
        }
	    
        if($id){
        	$form->add(array(
        			'name' => 'bas_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'bas_id',
        					'class' => 'form-control',
        					'required'=>'required',
        					'disabled' => 'disabled'
        			),
        	));
        }else{
        	$form->add(array(
        			'name' => 'bas_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'bas_id',
        					'class' => 'form-control',
        					'required'=>'required',
        			),
        	));
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
	        'name' => 'nas_ip',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'nas_ip',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'ip_pool',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'producer',
	            'class' => 'form-control',
	            'required'=>'required',
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
            $where->equalTo('bas_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'bas_id',
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
                                    'field' => 'bas_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'bas的id 已存在',
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
                                    'message' => 'bas名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            $where = new Where();
            $where->equalTo('nas_ip', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'nas_ip',
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
                                    'field' => 'nas_ip',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'nas_ip 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getIpRegex(),
                                    'field' => 'nas_ip',
                                    'message' => '非有效ip地址格式',
                                ),
                                
                            ),
                        ),
                    )
                    )
                );
            
        }else{
            $where = new Where();
            $where->equalTo('bas_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'bas_id',
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
                                    'field' => 'bas_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'bas的id 已存在',
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
                                    'message' => 'bas名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            
            $where = new Where();
            $where->equalTo('nas_ip', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'nas_ip',
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
                                    'field' => 'nas_ip',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'nas_ip 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getIpRegex(),
                                    'field' => 'nas_ip',
                                    'message' => '非有效ip地址格式',
                                ),
                                
                            ),
                        ),
                    )
                    )
                );
        }
//         $inputFilter->add(
//             $factory->createInput(
//                 array(
//                     'name'     => 'ip_pool',
//                     'required' => true,
//                     'filters'  => array(
//                         array('name' => 'StripTags'),
//                         array('name' => 'StringTrim'),
//                     ),
//                 )
//                 )
//             );
        
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
	
	public function getRowById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
	}
	
	
	public function _checkExcelData($sheetData,$sheetName){
	    $dataCheck = array();
	    $result = Util::checkImportRequired($sheetData, array('bas_id','bas_name','nas_ip','ip_pool'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportUnique($sheetData, array('bas_id','bas_name','nas_ip'), $sheetName);
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
	        
	        $errorRow = $sheetName.' 数据错误 第'. ($key + 1)."行 ";
	        $option = Util::getOption($value[$titleColumn['update']]);
	        //插入2，有更新1，没更新0
	        //bas_id	bas_name	nas_ip	ip_pool	description
	        if(in_array($option, array(0,1,2))){
	            $name = trim($value[$titleColumn['bas_name']]);
	            $basId = trim($value[$titleColumn['bas_id']]);
	            $ip = trim($value[$titleColumn['nas_ip']]);
	            if($option != 0){
                    if(!Util::checkIp($ip)){
                        $result['error'][] = $errorRow."nas_ip 非有效ip地址格式";
                    }
                    $ipPool = trim($value[$titleColumn['ip_pool']]);
                    if($ipPool){
                        $checkIpPool = true;
                        $ipPoolArray = explode(",", $ipPool);
                        foreach ($ipPoolArray as $ipPoolValue){
                            $v = explode('/', $ipPoolValue);
                            if(count($v) != 2){
                                $checkIpPool = false;
                            }else{
                                $firstIp = $v[0];
                                $ipCount = $v[1];
                                if(!preg_match(Util::$firstIpRegex, $firstIp) || !preg_match(Util::$ipCountRegex, $ipCount)){
                                    $checkIpPool = false;
                                }
                            }
                         }
                         if(!$checkIpPool){
                             $result['error'][] = $errorRow."ip_pool $ipPool 格式不正确";
                         }
                    }
                    
                    
	                if($option == 1){
	                    $currentRow = $this->fetchRow(Util::getWhereArr(array('bas_id' => $basId)));
	                    if(!$currentRow){
	                       $result['error'][] = $errorRow."bas_id 不存在";
	                    }
	                }
	                if($option == 2){
	                    $checkArray = array();
	                    $checkArray['bas_id'] = array('bas_id' => $basId);
	                    $checkArray['bas_name'] = array('name' => $name);
	                    $checkArray['nas_ip'] = array('nas_ip' => $ip);
	                    Util::checkRowExist($checkArray,$errorRow,$result,$this);
	                }
	            }
	        }
	    }
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
	
	public function insertData($sheetData,$sheetName){
	    
	    $result = array();
	    $result['error'] = array();
	    $titleColumn = array_flip($sheetData[0]);
	    
	    foreach ($sheetData as $key => $value){
	        if($key == 0 || $key == 1){
	            continue;
	        }
	        $errorRow = $sheetName.' 数据错误 第'. ($key + 1)."行 ";
	        $option = Util::getOption($value[$titleColumn['update']]);
	        //插入2，有更新1，没更新0
	        if(in_array($option, array(1,2))){
	            $data = array();
	            $data['user_id'] = $this->_userId;
	            $data['project_id'] = $this->_projectId;
	            $basId = trim($value[$titleColumn['bas_id']]);
	            $data['bas_id'] = $basId;
	            $data['name'] = trim($value[$titleColumn['bas_name']]);
	            $data['nas_ip'] = trim($value[$titleColumn['nas_ip']]);
	            $data['ip_pool'] = trim($value[$titleColumn['ip_pool']]);
	            $data['description'] = trim($value[$titleColumn['description']]);
                if($option == 1){
                    unset($data['user_id']);
                    unset($data['bas_id']);
                    $this->updateRowByBasId($data,$basId);
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
	
	public function updateRowByBasId($data,$basId){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where = array();
	    $where[] = $this->quoteInto('bas_id = ?', $basId);
	    $where[] = $this->quoteInto('project_id = ?', $this->_projectId);
	    return $this->tableGateway->update($data, $where);
	}
	
	public function checkInitData(){
		$result = array();
		$result['error'] = array();
		$checkResult = Util::checkUniqueColumn($this->_uniqueColumn,$this,$this->_name);
		Util::getError($result, $checkResult);
		return $result;
	}
}