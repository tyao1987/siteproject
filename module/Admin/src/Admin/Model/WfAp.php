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

class WfAp extends DbTable
{
    protected $_defaultNullFilter = array('warranty_time','second_ac_id','third_ac_id','serial');
	protected $_name = 'wf_ap';
	protected $_uniqueColumn = array('name','mac_address','serial');
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
	    if(strtolower($data['ip']) == 'dhcp'){
	        $data['ip'] = 'DHCP';
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
                ->setAttribute('id', 'sites_form')
                ->setAttribute('enctype', 'multipart/form-data');
        $controller = "ap";
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
        			'name' => 'ap_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'ap_id',
        					'class' => 'form-control',
        					'required'=>'required',
        					'disabled'=>'disabled',
        			),
        	));
        }else{
        	$form->add(array(
        			'name' => 'ap_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'ap_id',
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
	    
	    
	    //ap_group_id
	    $wfApgroup =  new WfApgroup();
	    $apGroupList = $wfApgroup->getList(array('is_delete' => 0));
	    $selectOption = array(null=>'未选择');
	    foreach ($apGroupList as $apGroup){
	        $selectOption[$apGroup['id']] = $apGroup['name'];
	    }
	    $form->add(array(
	        'name' => 'ap_group_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'ap_group_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => 'AP组',
	            'value_options' => $selectOption,
	        )
	        
	    ));
	    
	    //site_id
	    $wfSite =  new WfSite();
	    $siteList = $wfSite->getList(array('is_delete' => 0));
	    $selectOption = array(null=>'未选择');
	    foreach ($siteList as $site){
	        $selectOption[$site['id']] = $site['name'];
	    }
	    $form->add(array(
	        'name' => 'site_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'site_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '场点',
	            'value_options' => $selectOption,
	        )
	        
	    ));
	    
	    //ac_cluster_id
	    $wfAccluster =  new WfAccluster();
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
	    
	    //first_ac_id second_ac_id third_ac_id
	    $wfAc = new WfAc();
	    $acList = $wfAc->getList(array('is_delete' => 0));
	    $selectOption = array(null=>'未选择');
        foreach ($acList as $ac){
            $selectOption[$ac['id']] = $ac['name'].'|'.$ac['ac_cluster_id'].'|';
        }
	    $form->add(array(
	        'name' => 'first_ac_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'first_ac_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '主AC',
	            'value_options' => $selectOption,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'second_ac_id',
	        'type' => 'Select',
	        'isEmpty'=>true,
	        'attributes' => array(
	            'id'    => 'second_ac_id',
	            'class' => 'form-control',
	        ),
	        'options' => array(
	            'label' => '从AC',
	            'value_options' => $selectOption,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'third_ac_id',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'third_ac_id',
	            'class' => 'form-control',
	        ),
	        'options' => array(
	            'label' => '第三AC',
	            'value_options' => $selectOption,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'ip',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'ip',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'mac_address',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'mac_address',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'gps_e',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'gps_e',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'gps_n',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'gps_n',
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
	        'name' => 'serial',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'serial',
	            'class' => 'form-control',
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
            $where->equalTo('ap_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ap_id',
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
                                    'field' => 'ap_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'ap的id 已存在',
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
                                    'message' => 'ap名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            $where = new Where();
            $where->equalTo('mac_address', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'mac_address',
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
                                    'field' => 'mac_address',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'mac地址 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getMacAddressRegex(),
                                    'field' => 'mac_address',
                                    'message' => 'mac地址 格式错误',
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
            
        }else{
            $where = new Where();
            $where->equalTo('ap_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'ap_id',
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
                                    'field' => 'ap_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'ap的id 已存在',
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
                                    'message' => 'ap名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            $where = new Where();
            $where->equalTo('mac_address', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'mac_address',
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
                                    'field' => 'mac_address',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => 'mac地址 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                            array(
                                'name'    => 'Regex',
                                'options' => array(
                                    'pattern' => Util::getMacAddressRegex(),
                                    'field' => 'mac_address',
                                    'message' => 'mac地址 格式错误',
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
            
        }
        
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'second_ac_id',
                    'required' => false,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                )
                )
            );
        
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'third_ac_id',
                    'required' => false,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                )
                )
            );
        
        
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'ip',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'Regex',
                            'options' => array(
                                'pattern' => '/^((25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))|dhcp$/is',
                                'field' => 'ip',
                                'message' => '非有效ip地址格式',
                            ),
                            
                        ),
                    ),
                )
                )
            );
        
        
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'gps_e',
                    'required' => false,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
//                     'validators' => array(
//                         array(
//                             'name'    => 'Regex',
//                             'options' => array(
//                                 'pattern' => Util::getGpsRegex(),
//                                 'field' => 'gps_e',
//                                 'message' => '经度 格式错误',
//                             ),
                            
//                         ),
//                     ),
                )
                )
            );
        
        $inputFilter->add(
            $factory->createInput(
                array(
                    'name'     => 'gps_n',
                    'required' => false,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
//                     'validators' => array(
//                         array(
//                             'name'    => 'Regex',
//                             'options' => array(
//                                 'pattern' => Util::getGpsRegex(),
//                                 'field' => 'gps_n',
//                                 'message' => '纬度 格式错误',
//                             ),
                            
//                         ),
//                     ),
                )
                )
            );

        
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
	    if(strtolower($data['ip']) == 'dhcp'){
	        $data['ip'] = 'DHCP';
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
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
	    $result = Util::checkImportRequired($sheetData, array('apid','apname','apgroupid',
	                                       'siteid','acclusterid','primaryacid',
	                                       'apip','apmac','producer'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportUnique($sheetData, array('apid','apname','apmac','serial'), $sheetName);
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
	        
	        $name = trim($value[$titleColumn['apname']]);
	        $apId = trim($value[$titleColumn['apid']]);
	        $option = (int) $value[$titleColumn['update']];
	        $warrantyTime = trim($value[$titleColumn['warrantytime']]);
	        $serial = trim($value[$titleColumn['serial']]);
	        $ip = trim($value[$titleColumn['apip']]);
	        $macAddress = trim($value[$titleColumn['apmac']]);
	        $gpsE = trim($value[$titleColumn['gps_e']]);
	        $gpsN = trim($value[$titleColumn['gps_n']]);
	        $first = trim($value[$titleColumn['primaryacid']]);
	        $second = trim($value[$titleColumn['secondaryacid']]);
	        $third = trim($value[$titleColumn['thirdacid']]);
	        
	        //插入2，有更新1，没更新0
	        if(in_array($option, array(0,1,2))){
	            if($option != 0){
	                
// 	                if($gpsE !== '' && $gpsE !== null){
// 	                    if(!preg_match(Util::getGpsRegex(), $gpsE)){
// 	                        $result['error'][] = $errorRow."gps_e 格式不正确";
// 	                    }
// 	                }
	                
// 	                if($gpsN !== '' && $gpsN !== null){
// 	                    if(!preg_match(Util::getGpsRegex(), $gpsN)){
// 	                        $result['error'][] = $errorRow."gps_n 格式不正确";
// 	                    }
// 	                }
	                
	                if(($second !== '' && $second !== null) || ($third !== '' && $third !== null)){
	                    if($first == $second || $second == $third || $first == $third){
	                        $result['error'][] = $errorRow."主AC,从AC,第三AC重复";
	                    }
	                }
	                
	                if(strtolower($ip) != 'dhcp'){
	                    if(!Util::checkIp($ip)){
	                        $result['error'][] = $errorRow."apip 非有效ip地址格式";
	                    }
	                }
	                if(!Util::checkMacAddress($macAddress)){
	                    $result['error'][] = $errorRow."apmac 非有效ip地址格式";
	                }
	                
	                if($warrantyTime !== "" && $warrantyTime !== null) {
	                    if(!Util::checkExcelDate($warrantyTime)){
	                        $result['error'][] = $errorRow."warrantytime 非有效日期格式";
	                    }
	                }
	                
	                if($option == 1){
	                    $currentRow = $this->fetchRow(Util::getWhereArr(array('ap_id' => $apId)));
	                    if(!$currentRow){
	                       $result['error'][] = $errorRow."apid 不存在";
	                    }else{
// 	                        if($name != $currentRow['name']){
// 	                            $checkArray = array();
// 	                            $checkArray['apname'] = array('name' => $name);
// 	                            Util::checkRowExist($checkArray,$errorRow,$result,$this);
// 	                        }
	                        
// 	                        if($serial != $currentRow['serial'] && $serial !== '' && $serial !== null){
//                                 $checkArray = array();
//                                 $checkArray['serial'] = array('serial' => $serial);
//                                 Util::checkRowExist($checkArray,$errorRow,$result,$this);
// 	                        }
	                    }
	                }
	                if($option == 2){
	                    $checkArray = array();
	                    $checkArray['apid'] = array('ap_id' => $apId);
	                    $checkArray['apname'] = array('name' => $name);
	                    if($serial !== '' && $serial !== null){
	                        $checkArray['serial'] = array('serial' => $serial);
	                    }
	                    
	                    $checkArray['acmac'] = array('mac_address' => $macAddress);
	                    Util::checkRowExist($checkArray,$errorRow,$result,$this);
	                }
	                
	                
	            }
	        }else{
	            $result['error'][] = $errorRow."update 只能是 0,1,2";
	        }
	    }
	    return $result;
	}
	
	public function insertData($sheetData,$sheetName){
	        
        $result = array();
        $result['error'] = array();
        $titleColumn = array_flip($sheetData[0]);
        $wfSite = new WfSite();
        $wfApgroup = new WfApgroup();
        $wfAccluster = new WfAccluster();
        $wfAc = new WfAc();
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
                $apId = trim($value[$titleColumn['apid']]);
                $data['ap_id'] = $apId;
                $data['name'] = trim($value[$titleColumn['apname']]);
                $data['gps_e'] = trim($value[$titleColumn['gps_e']]);
                $data['gps_n'] = trim($value[$titleColumn['gps_n']]);
                $data['description'] = trim($value[$titleColumn['description']]);
                $data['warranty_time'] = Util::getExcelDate(trim($value[$titleColumn['warrantytime']]));
                $data['type'] = trim($value[$titleColumn['type']]);
                $data['producer'] = trim($value[$titleColumn['producer']]);
                $data['serial'] = trim($value[$titleColumn['serial']]);
                $data['mac_address'] = trim($value[$titleColumn['apmac']]);
                $data['ip'] = trim($value[$titleColumn['apip']]);
                $siteId = trim($value[$titleColumn['siteid']]);
                $row = $wfSite->fetchRow(Util::getWhereArr(array('is_delete'=>0,'site_id'=>$siteId)));
                if(!$row){
                    $result['error'][] = $errorRow."siteid $siteId 不存在";
                }
                $data['site_id'] = $row['id'];
                
                $apgroupId = trim($value[$titleColumn['apgroupid']]);
                $row = $wfApgroup->fetchRow(Util::getWhereArr(array('is_delete'=>0,'ap_group_id'=>$apgroupId)));
                if(!$row){
                    $result['error'][] = $errorRow."apgroupid $apgroupId 不存在";
                }
                $data['ap_group_id'] = $row['id'];
                
                $acclusterId = trim($value[$titleColumn['acclusterid']]);
                $row = $wfAccluster->fetchRow(Util::getWhereArr(array('is_delete'=>0,'ac_cluster_id'=>$acclusterId)));
                if(!$row){
                    $result['error'][] = $errorRow."acclusterid $acclusterId 不存在";
                }
                $data['ac_cluster_id'] = $row['id'];
                
                $acId = trim($value[$titleColumn['primaryacid']]);
                $row = $wfAc->fetchRow(Util::getWhereArr(array('is_delete'=>0,'ac_id'=>$acId)));
                if(!$row){
                    $result['error'][] = $errorRow."primaryacid $acId 不存在";
                }else{
                    if($row['ac_cluster_id'] != $data['ac_cluster_id']){
                        $result['error'][] = $errorRow."primaryacid $acId 不属于 ac组 ".$data['ac_cluster_id'];
                    }
                }
                $data['first_ac_id'] = $row['id'];
                
                $acId = trim($value[$titleColumn['secondaryacid']]);
                if($acId !== '' && $acId !== null){
                    $row = $wfAc->fetchRow(Util::getWhereArr(array('is_delete'=>0,'ac_id'=>$acId)));
                    if(!$row){
                        $result['error'][] = $errorRow."secondaryacid $acId 不存在";
                    }else{
                        if($row['ac_cluster_id'] != $data['ac_cluster_id']){
                            $result['error'][] = $errorRow."secondaryacid $acId 不属于 ac组 ".$data['ac_cluster_id'];
                        }
                    }
                    $data['second_ac_id'] = $row['id'];
                }
                $acId = trim($value[$titleColumn['thirdacid']]);
                if($acId !== '' && $acId !== null){
                    $row = $wfAc->fetchRow(Util::getWhereArr(array('is_delete'=>0,'ac_id'=>$acId)));
                    if(!$row){
                        $result['error'][] = $errorRow."thirdacid $acId 不存在";
                    }else{
                        if($row['ac_cluster_id'] != $data['ac_cluster_id']){
                            $result['error'][] = $errorRow."thirdacid $acId 不属于 ac组 ".$data['ac_cluster_id'];
                        }
                    }
                    $data['third_ac_id'] = $row['id'];
                }
                
                $first = $data['first_ac_id'];
                $second = $data['second_ac_id'];
                $third = $data['third_ac_id'];
                if($second != '' || $third != ''){
                    if($first == $second || $second == $third || $first == $third){
                        $result['error'][] = $errorRow."主AC,从AC,第三AC重复";
                    }
                }
                
                if($result['error']){
                    continue;
                }
                if($option == 1){
                    unset($data['user_id']);
                    unset($data['ap_id']);
                    $this->updateRowByApId($data,$apId);
                }
                if($option == 2){
                    $id = $this->insertRow($data);
                }
            }
        }
        
        $checkResult = Util::checkUniqueColumn($this->_uniqueColumn,$this,$this->_name);
        Util::getError($result, $checkResult);
        return $result;
	}
	
	public function updateRowByApId($data,$apId){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where[] = $this->quoteInto('ap_id = ?', $apId);
	    $where[] = $this->quoteInto('project_id = ?', $this->_projectId);
	    return $this->tableGateway->update($data, $where);
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