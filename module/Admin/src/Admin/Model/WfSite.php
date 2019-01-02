<?php
namespace Admin\Model;


use Application\Model\DbTable;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

use Zend\Form\Form;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;

use Admin\Util\Util;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Validator\Db\NoRecordExists;
use Zend\Db\Sql\Where;

class WfSite extends DbTable
{
    protected $_defaultNullFilter = array('district','is_mac_auth','is_customized','is_private');
    protected $operator = array('全部','电信','移动','联通','其他');
    protected $selectResult = array('未知','是','否');
    protected $_district = array(0=>'未知');
    protected $_uniqueColumn = array('name');
	protected $_name = 'wf_site';
	protected $_projectId = 0;
	protected $_userId = 0;
	
	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
		$selectOptions = array();
		foreach ($this->operator as $key => $value){
		    $selectOptions[$value] = $value; 
		}
		$this->operator = $selectOptions;
		$selectOptions = array();
		foreach ($this->selectResult as $key => $value){
		    $selectOptions[$value] = $value;
		}
		$this->selectResult = $selectOptions;
		$distinct = new WfDistrict();
	    $distinctList = $distinct->getList();
	    if($distinctList){
	        foreach ($distinctList as $key=> $value){
	            $this->_district[$value['id']] = $value['name']; 
	        }
	    }
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
                ->setAttribute('id', 'sites_form')
                ->setAttribute('enctype', 'multipart/form-data');
        $controller = "sites";
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
        			'name' => 'site_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'site_id',
        					'class' => 'form-control',
        					'required'=>'required',
        					'disabled' => 'disabled'
        			),
        	));
        }else{
        	$form->add(array(
        			'name' => 'site_id',
        			'type' => 'Text',
        			'attributes' => array(
        					'id'    => 'site_id',
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
	        'name' => 'address',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'address',
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
	        'name' => 'image_url',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'image_url',
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
	        'name' => 'network_user',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'network_user',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'project_user',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'project_user',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'maintenancer',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'maintenancer',
	            'class' => 'form-control',
	            'required'=>'required',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'district_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'district_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '行政区划',
	            'value_options' => $this->_district,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'cover_range',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'cover_range',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'idle_timeout',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'idle_timeout',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'grace_period',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'grace_period',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'session_timeout',
	        'type' => 'Text',
	        'attributes' => array(
	            'id'    => 'session_timeout',
	            'class' => 'form-control',
	        ),
	    ));
	    
	    $form->add(array(
	        'name' => 'is_mac_auth',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'is_mac_auth',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '是否支持mac认证',
	            'value_options' => $this->selectResult,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'is_private',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'is_private',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '是否私网',
	            'value_options' => $this->selectResult,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'is_customized',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'is_customized',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '是否自建',
	            'value_options' => $this->selectResult,
	        )
	        
	    ));
	    
	    $form->add(array(
	        'name' => 'operator',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'operator',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '运营商',
	            'value_options' => $this->operator,
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
	    
	    //ac_id
	    $wfAudit = new WfAudit();
	    $auditList = $wfAudit->getList(array('is_delete' => 0));
	    $selectOption = array(null=>'未选择');
	    foreach ($auditList as $audit){
	        $selectOption[$audit['id']] = $audit['name'];
	    	//$selectOption[$audit['id']] = $audit['audit_id'];
	    }
	    $form->add(array(
	        'name' => 'audit_id',
	        'required'=>'required',
	        'type' => 'Select',
	        'attributes' => array(
	            'id'    => 'audit_id',
	            'class' => 'form-control',
	            'required'=>true,
	        ),
	        'options' => array(
	            'label' => '审计设备号',
	            'value_options' => $selectOption,
	        )
	        
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
            $where->equalTo('site_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'site_id',
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
                                    'field' => 'site_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => '场点id 已存在',
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
                                    'message' => '场点名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            
        }else{
            $where = new Where();
            $where->equalTo('site_id', null);
            $where->equalTo('project_id', $this->_projectId);
            $where->notEqualTo('id', $id);
            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'site_id',
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
                                    'field' => 'site_id',
                                    'adapter' => $this->tableGateway->getAdapter(),
                                    'message' => '场点id 已存在',
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
                                    'message' => '场点名称 已存在',
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
	    
	    $result = Util::checkImportRequired($sheetData, array('siteid','sitename','address','maintenancer','audit_id','operator'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportUnique($sheetData, array('siteid','sitename'), $sheetName);
	    Util::getError($dataCheck, $result);
	    $result = Util::checkImportEnum($sheetData, 
	                               array('if_customized' => array(0,1,2),'if_private' => array(0,1,2),
	                                   'district' => array_values($this->_district),'is_mac_auth' => array('未知','是','否'),
	                                   'operator' => $this->operator), $sheetName);
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
	            if($option != 0){
	                $name = trim($value[$titleColumn['sitename']]);
	                $siteId = trim($value[$titleColumn['siteid']]);
	                $gpsE = trim($value[$titleColumn['gps_e']]);
	                $gpsN = trim($value[$titleColumn['gps_n']]);
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
	                if($option == 1){
	                    $currentRow = $this->fetchRow(Util::getWhereArr(array('site_id' => $siteId)));
	                    if(!$currentRow){
	                       $result['error'][] = $errorRow."siteid 不存在";
	                    }
// 	                    else{
// 	                        if($name != $currentRow['name']){
// 	                            $checkArray = array();
// 	                            $checkArray['sitename'] = array('name' => $name);
// 	                            Util::checkRowExist($checkArray,$errorRow,$result,$this);
// 	                        }
	                        
// 	                    }
	                }
	                if($option == 2){
	                    $checkArray = array();
	                    $checkArray['siteid'] = array('site_id' => $siteId);
	                    $checkArray['sitename'] = array('name' => $name);
	                    Util::checkRowExist($checkArray,$errorRow,$result,$this);
	                }
	                
	                
	            }
	        }else{
	            $result['error'][] = $errorRow."update 只能是 0,1,2";
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
	    //$authIdentity = Auth::getIdentity();
	    $titleColumn = array_flip($sheetData[0]);
	    $wfAudit = new WfAudit();
	    $wfTag = new WfTag();
	    $wfDistrict = new WfDistrict();
	    $siteTag = new WfSiteTag();
	    foreach ($sheetData as $key => $value){
	        if($key == 0 || $key == 1){
	            continue;
	        }
	        $errorRow = $sheetName.' 数据错误 第'. ($key + 1)."行 ";
	        $option = Util::getOption($value[$titleColumn['update']]);
	        //插入2，有更新1，没更新0
	        if(in_array($option, array(1,2))){
	            //tags				
	            //维护单位(必填)	所属运营商(必填, '全部','电信','移动','联通','其他')	标签	是否自建（0为否 1-是 2-未知）	审计设备编号(必填)	是否私网（0为否 1-是 2-未知）	描述	是否支持mac认证(未知,是,否)	session timeout	idle timeout	grace period	网络负责人	项目负责人	行政区划(未知或重庆区域)	覆盖范围
	            $data = array();
	            //$data['user_id'] = $authIdentity['id'];
	            $data['user_id'] = $this->_userId;
	            $data['project_id'] = $this->_projectId;
	            $siteId = trim($value[$titleColumn['siteid']]);
	            $data['site_id'] = $siteId;
	            $data['name'] = trim($value[$titleColumn['sitename']]);
	            $data['address'] = trim($value[$titleColumn['address']]);
	            $data['gps_e'] = trim($value[$titleColumn['gps_e']]);
	            $data['gps_n'] = trim($value[$titleColumn['gps_n']]);
	            $data['maintenancer'] = trim($value[$titleColumn['maintenancer']]);
	            $data['operator'] = trim($value[$titleColumn['operator']]);
	            $data['session_timeout'] = trim($value[$titleColumn['session_timeout']]);
	            $data['idle_timeout'] = trim($value[$titleColumn['idle_timeout']]);
	            $data['grace_period'] = trim($value[$titleColumn['grace_period']]);
	            $data['description'] = trim($value[$titleColumn['Description']]);
	            $data['cover_range'] = trim($value[$titleColumn['cover_range']]);
	            $districtValue = trim($value[$titleColumn['district']]);
	            if($districtValue !== null && $districtValue !== ''){
	                if($districtValue == '未知'){
	                    $data['district_id'] = 0;
	                }else{
	                    $district = $wfDistrict->fetchRow(Util::getWhereArr(array('is_delete'=>0,'name'=>$districtValue)));
	                    if(!$district){
	                        $result['error'][] = $errorRow."district $district 不存在";
	                        //continue;
	                    }else{
	                        $data['district_id'] = $district['id'];
	                    }
	                }
	                
	            }
	            
	            $data['network_user'] = trim($value[$titleColumn['user_id1_c']]);
	            $data['project_user'] = trim($value[$titleColumn['user_id_c']]);
	            $data['is_mac_auth'] = trim($value[$titleColumn['is_mac_auth']]);
	            $selectOption = array(0=>'否',1=>'是',2=>'未知');
	            $isCustomized = (int) trim($value[$titleColumn['if_customized']]);
	            $data['is_customized'] = $selectOption[$isCustomized];
	            $isPrivate = (int) trim($value[$titleColumn['if_private']]);
	            $data['is_private'] = $selectOption[$isPrivate];
	            $auditId = trim($value[$titleColumn['audit_id']]);
	            $tagList = trim($value[$titleColumn['tags']]);
	            $tagIdArray = array();
	            if($tagList){
	                $tagArray = explode(",", $tagList);
	                $tagArray = array_unique($tagArray);
	                foreach ($tagArray as $tagValue){
	                    $row = $wfTag->fetchRow(Util::getWhereArr(array('is_delete'=>0,'tag_id'=>$tagValue)));
	                    if(!$row){
	                        $result['error'][] = $errorRow."tag_id $tagValue 不存在";
	                        //continue;
	                    }
	                    $tagIdArray[] = $row['id'];
	                }
	                
	            }else{
	                $tagArray = array();
	            }
	            
	            $audit = $wfAudit->fetchRow(Util::getWhereArr(array('is_delete' => 0,'audit_id' => $auditId)));
	            if(!$audit){
	                $result['error'][] = $errorRow."audit_id 不存在";
	                //continue;
	            }else{
	                $data['audit_id'] = $audit['id'];
	            }
	            if($result['error']){
	                continue;
	            }
                if($option == 1){
                    unset($data['user_id']);
                    unset($data['site_id']);
                    $currentRow = $this->fetchRow(Util::getWhereArr(array('site_id' => $siteId)));
                    $id = $currentRow['id'];
                    $this->updateRowBySiteId($data,$siteId);
                }
                if($option == 2){
                    $id = $this->insertRow($data);
                }
                $siteTag->updateSiteTag($id, $tagIdArray);
	        }
	    }
	    $checkResult = Util::checkUniqueColumn($this->_uniqueColumn,$this,$this->_name);
	    Util::getError($result, $checkResult);
	    return $result;
	}
	
	public function updateRowBySiteId($data,$siteId){
	    if(isset($this->_defaultNullFilter)){
	        $data = Util::emptyToNull($data, $this->_defaultNullFilter);
	    }
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    unset($data['update_time']);
	    $where = array();
	    $where[] = $this->quoteInto('site_id = ?', $siteId);
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