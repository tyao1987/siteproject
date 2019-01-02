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

class WfAppresource extends DbTable
{
    protected $_defaultNullFilter = array('floor','coordinate_x','coordinate_y','altitude','gps_precision','district_id');
	protected $_name = 'wf_app_resource';
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
                ->setAttribute('id', 'appresource_form')
                ->setAttribute('enctype', 'multipart/form-data');
        $controller = "appresource";
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
    		'name' => 'open_time',
    		'type' => 'Text',
    		'attributes' => array(
    			'id'    => 'open_time',
    			'class' => 'form-control',
    		),
	    ));
	    
	    $selectOption = array(null=>'未选择','food'=>'食品','movie'=>'电影');
	    $form->add(array(
	    		'name' => 'category',
	    		'required'=>'required',
	    		'type' => 'Select',
	    		'attributes' => array(
	    				'id'    => 'category',
	    				'class' => 'form-control',
	    				'required'=>true,
	    		),
	    		'options' => array(
	    				'label' => '分类',
	    				'value_options' => $selectOption,
	    		)
	    		
	    ));
	    
	    $form->add(array(
	    		'name' => 'max_online',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'max_online',
	    				'class' => 'form-control',
	    				'required'=>'required',
	    		),
	    ));
	    
	    $selectOption = array(null=>'未选择','ishanghai'=>'ishanghai','ipudong'=>'ipudong');
	    $form->add(array(
	    		'name' => 'brand',
	    		'required'=>'required',
	    		'type' => 'Select',
	    		'attributes' => array(
	    				'id'    => 'brand',
	    				'class' => 'form-control',
	    				'required'=>true,
	    		),
	    		'options' => array(
	    				'label' => '品牌',
	    				'value_options' => $selectOption,
	    		)
	    		
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
	    		'name' => 'display_level',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'display_level',
	    				'class' => 'form-control',
	    				'required'=>'required',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'floor',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'floor',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'coordinate_x',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'coordinate_x',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'coordinate_y',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'coordinate_y',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'altitude',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'altitude',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'phone',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'phone',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'gps_precision',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'gps_precision',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'network_speed',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'network_speed',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'business_time',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'business_time',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'district_id',
	    		'type' => 'Text',
	    		'attributes' => array(
	    				'id'    => 'district_id',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'cover_point',
	    		'type' => 'Textarea',
	    		'attributes' => array(
	    				'id'    => 'cover_point',
	    				'class' => 'form-control',
	    		),
	    ));
	    
	    $form->add(array(
	    		'name' => 'cover_range',
	    		'type' => 'Textarea',
	    		'attributes' => array(
	    				'id'    => 'cover_range',
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
                                    'message' => '资源名称 已存在',
                                    'exclude' => $where,
                                ),
                            ),
                        ),
                    )
                )
            );
            
        }else{
        	
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
                                    'message' => '资源名称 已存在',
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
                    'name'     => 'open_time',
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
        						'name'     => 'address',
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
        						'name'     => 'phone',
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
        						'name'     => 'network_speed',
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
        						'name'     => 'business_time',
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
        						'name'     => 'district_id',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+$/i',
        												'field' => 'district_id',
        												'message' => '行政编号格式错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'max_online',
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+$/i',
        												'field' => 'max_online',
        												'message' => '最大在线人数格式错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'display_level',
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+$/i',
        												'field' => 'display_level',
        												'message' => '展示级别格式错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'floor',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+(\.\d+)?$/is',
        												'field' => 'floor',
        												'message' => '楼层格式错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'gps_precision',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+(\.\d+)?$/is',
        												'field' => 'gps_precision',
        												'message' => '精度格式错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        
        
        
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'coordinate_x',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+(\.\d+)?$/is',
        												'field' => 'coordinate_x',
        												'message' => '楼层坐标X错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'coordinate_y',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+(\.\d+)?$/is',
        												'field' => 'coordinate_y',
        												'message' => '楼层坐标Y错误',
        										),
        										
        								),
        						),
        				)
        				)
        		);
        $inputFilter->add(
        		$factory->createInput(
        				array(
        						'name'     => 'altitude',
        						'required' => false,
        						'filters'  => array(
        								array('name' => 'StripTags'),
        								array('name' => 'StringTrim'),
        						),
        						'validators' => array(
        								array(
        										'name'    => 'Regex',
        										'options' => array(
        												'pattern' => '/^\d+(\.\d+)?$/is',
        												'field' => 'altitude',
        												'message' => '海拔格式错误',
        										),
        										
        								),
        						),
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