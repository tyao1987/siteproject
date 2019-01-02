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

class WfSiteTag extends DbTable
{
	protected $_name = 'wf_site_tag';

	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
	}

	public function insertRow($data){
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $this->insert($data);
	    return $this->tableGateway->lastInsertValue;
	}
	
	public function getRowById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
	}
	
	public function getList($where,$order = array()){
	    $data = $this->tableGateway->getAdapter();
	    $select = new Select();
	    $select->from($this->_name);
	    $this->_select->where($where);
	    if($order){
	        $select->order($order);
	    }
	    $sql = $this->_select->getSqlString($data->getPlatform());
	    //echo $sql;exit;
	    return $this->fetchAll($sql);
	}
	
	public function deleteById($id){
	    //$dbAdapter = $this->tableGateway->getAdapter();
	    //$sql = new Sql ($dbAdapter);
	    $ret = $this->tableGateway->delete($this->quoteInto('id=?', $id));
	        
	    return $ret;
	}
	
	public function updateSiteTag($siteId,$tagArray){
	    $this->tableGateway->delete($this->quoteInto('site_id=?', $siteId));
	    if($tagArray){
	        $data = array();
	        $authIdentity = Auth::getIdentity();
	        $data['site_id'] = $siteId;
	        foreach ($tagArray as $tag){
	            $data['tag_id'] = $tag;
	            $this->insertRow($data);
	        }
	    }
	}
	
	
}