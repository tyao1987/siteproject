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

class WfAc extends DbTable
{
    protected $_defaultNullFilter = array('serial','warranty_time');
	protected $_name = 'wf_ac';
	protected $_uniqueColumn = array('name','ac_mip','serial');
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
                ->setAttribute('id', 'ac_form')
                ->setAttribute('enctype', 'multipart/form-data');
        $controller = "ac";
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
	    			'name' => 'ac_id',
	    			'type' => 'Text',
	    			'attributes' => array(
	    					'id'    => 'ac_id',
	    					'class' => 'form-control',
	    					'required'=>'required',
	    					'disabled' => 'disabled'
	    			),
	    	));
	    }else{
	    	$form->add(array(
	    			'name' => 'ac_id',
	    			'type' => 'Text',
	    			'attributes' => array(
	    					'id'    => 'ac_id',
	    					'class' => 'form-control',
	    					'required'=>'required',
	    			),
	    	));
	    }
	    
	    if($id){
	    	$form->add(array(
	    			'name' => 'name',
	    			'type' => 'Text',
	    			'attributes' => array(
	    					'id'    => 'name',
	    					'class' => 'form-control',
	    					'required'=>'required',
	    					'disabled' => 'disabled'
	    			),
	    	));
	    }else{
	    	$form->add(array(
	    			'name' => 'name',
	    			'type' => 'Text',
	    			'attributes' => array(
	    					'id'    => 'name',
	    					'class' => 'form-control',
	    					'required'=>'required',
	    			),
	    	));
	    }
	    
	    
	    
	    $form->add(array(
	        'name' => 'serial',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'serial',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'ac_mip',
	        'type' => 'Text',
	        'required'=>'required',
	        'attributes' => array(
	            'id'    => 'ac_mip',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'address',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'address',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'producer',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'producer',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'warranty_time',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'warranty_time',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'type',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'type',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    //ac_cluster_id
	    $wfAccluster = new WfAccluster();
	    $acclusterList = $wfAccluster->getList(array('is_delete' => 0));
	    $selectOption = array(null=>'未选择');
	    foreach ($acclusterList as $accluster){
	        $selectOption[$accluster['id']] = $accluster['name'];
	    }
	    $form->add(array(
	        'name' => 'ac_cluster_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'ac_cluster_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => 'AC组',
	            'value_options' => $selectOption,
	        )
	        
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
            $where->equalTo('ac_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ac_id',
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
                                    'field' => 'ac_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'AC的id 已存在',
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
                                    'message' => 'AC名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            $where = new Where();
            $where->equalTo('serial', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'serial',
                        'required' => false,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => $this->_name,
                                    'field' => 'serial',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => '序列号 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                    )
                );
            $where = new Where();
            $where->equalTo('ac_mip', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ac_mip',
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
                                    'field' => 'ac_mip',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'AC管理ip 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getIpRegex(),
                                    'field' => 'ac_mip',
                                    'message' => '非有效ip地址格式',
                                ),
                                
                            ),
                        ),
                    )
                    )
                );
            
        }else{
            $where = new Where();
            $where->equalTo('ac_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ac_id',
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
                                    'field' => 'ac_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'AC的id 已存在',
                                    'exclude' => $where,
                                ),
    	                        
                            ),
                        ),
                    )
                )
            );
            $where = new Where();
            $where->equalTo('serial', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'serial',
                        'required' => false,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name'    => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => $this->_name,
                                    'field' => 'serial',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => '序列号 已存在',
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
                                    'message' => 'AC名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            
            $where = new Where();
            $where->equalTo('ac_mip', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ac_mip',
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
                                    'field' => 'ac_mip',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'AC管理ip 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getIpRegex(),
                                    'field' => 'ac_mip',
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
//                     'name'     => 'ac_cluster_id',
//                     'required' => false,
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
	    $result = Util::checkImportRequired($sheetData, array('acid','acname','acmip','producer','acclusterid'), $sheetName);
	    //$result = Util::checkImportRequired($sheetData, array('acid','acname','acmip','producer'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportUnique($sheetData, array('acid','acname','acmip','serial'), $sheetName);
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
	        if(in_array($option, array(0,1,2))){
	            $name = trim($value[$titleColumn['acname']]);
	            $acId = trim($value[$titleColumn['acid']]);
	            $ip = trim($value[$titleColumn['acmip']]);
	            $serial = trim($value[$titleColumn['serial']]);
	            $warrantyTime = trim($value[$titleColumn['warrantytime']]);
	            if($option != 0){
                    if(!Util::checkIp($ip)){
                        $result['error'][] = $errorRow."acmip 非有效ip地址格式";
                    }
                    if($warrantyTime !== "" && $warrantyTime !== null) {
                        if(!Util::checkExcelDate($warrantyTime)){
                            $result['error'][] = $errorRow."warrantytime 非有效日期格式";
                        }
	                }
	                if($option == 1){
	                    $currentRow = $this->fetchRow(Util::getWhereArr(array('ac_id' => $acId)));
	                    if(!$currentRow){
	                       $result['error'][] = $errorRow."acid 不存在";
	                    }else{
// 	                        if($name != $currentRow['name']){
// 	                            $checkArray = array();
// 	                            $checkArray['acname'] = array('name' => $name);
// 	                            Util::checkRowExist($checkArray,$errorRow,$result,$this);
// 	                        }
	                        
//                             if($ip != $currentRow['ac_mip']){
//                                 $checkArray = array();
//                                 $checkArray['acmip'] = array('ac_mip' => $ip);
//                                 Util::checkRowExist($checkArray,$errorRow,$result,$this);
//                             }
	                        
// 	                        if($serial != $currentRow['serial']){
// 	                            $checkArray = array();
// 	                            $checkArray['serial'] = array('serial' => $serial);
// 	                            Util::checkRowExist($checkArray,$errorRow,$result,$this);
// 	                        }
	                    }
	                }
	                if($option == 2){
	                    
	                    $checkArray = array();
	                    $checkArray['acid'] = array('ac_id' => $acId);
	                    $checkArray['acname'] = array('name' => $name);
	                    $checkArray['acmip'] = array('ac_mip' => $ip);
	                    $checkArray['serial'] = array('serial' => $serial);
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
	    $wfAccluster = new WfAccluster();
	    
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
	            $acId = trim($value[$titleColumn['acid']]);
	            $data['ac_id'] = $acId;
	            $data['name'] = trim($value[$titleColumn['acname']]);
	            $data['address'] = trim($value[$titleColumn['acaddress']]);
	            $data['ac_mip'] = trim($value[$titleColumn['acmip']]);
	            $data['serial'] = trim($value[$titleColumn['serial']]);
	            $data['producer'] = trim($value[$titleColumn['producer']]);
	            $data['warranty_time'] = Util::getExcelDate(trim($value[$titleColumn['warrantytime']]));
	            $acclusterId = trim($value[$titleColumn['acclusterid']]);
	            if($acclusterId !== null && $acclusterId !== ''){
	            	$accluster = $wfAccluster->fetchRow(Util::getWhereArr(array('is_delete' => 0,'ac_cluster_id' => $acclusterId)));
	            	if(!$accluster){
	            		$result['error'][] = $errorRow."acclusterid 不存在";
	            		continue;
	            	}
	            	$data['ac_cluster_id'] = $accluster['id'];
	            }
	            $data['type'] = trim($value[$titleColumn['actype']]);
	            $data['description'] = trim($value[$titleColumn['description']]);
                if($option == 1){
                    unset($data['user_id']);
                    unset($data['acid']);
                    $this->updateRowByAcId($data,$acId);
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
	
	public function updateRowByAcId($data,$auditId){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where = array();
	    $where[] = $this->quoteInto('ac_id = ?', $auditId);
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