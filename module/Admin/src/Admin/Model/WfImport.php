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
use Admin\Model\Auth;
use Admin\Util\Util;
use Admin\Model\WfAc;
use Admin\Model\WfTag;
use Admin\Model\WfAccluster;
use Admin\Model\WfBas;
use Admin\Model\WfAp;
use Admin\Model\WfApgroup;
use Admin\Model\WfAudit;
use Admin\Model\WfDistrict;
use Admin\Model\WfPortal;
use Admin\Model\WfSiteTag;
use Admin\Model\WfSite;
use Zend\Db\Sql\Where;

class WfImport extends DbTable
{
	protected $_name = 'wf_import';

	protected $_projectId = 0;
	
	protected $_userId = 0;
	
	public function objToArray($obj) {
		$ret = array();
		if(is_array($obj) || is_object($obj)){
			foreach($obj as $key => $value) {
				$ret[$key] = self::objToArray($value);
			}
		}else {
			return $obj;
		}
		return $ret;
	}
	
	function __construct(){
		$this->setTableGateway("cmsdb", $this->_name);
		$this->_select = $this->tableGateway->getSql()->select();
		$identity = Auth::getIdentity();
		$this->_projectId = (int)$identity['project_id'];
		$this->_userId = (int)$identity['id'];
	}

	
	public function insertData($excelData){
	    $result = array();
	    $result['error'] = array();
	    $this->beginTransaction();
	    try {
	        foreach ($excelData as $sheetName => $sheetData){
	            if(count($sheetData) > 2){
	                $class = 'Admin\\Model\\Wf'.$sheetName;
	                $obj = new $class();
	                $checkResult = $obj->insertData($sheetData,$sheetName);
	                Util::getError($result, $checkResult);
	            }
	        }
	        if(!$result['error']){
	            $this->commit();
	        }else{
	            $this->rollback();
	        }
	        
	    }catch (\Exception $e) {
	        $this->rollback();
	        \Application\Exception::log($e);
	        if(APPLICATION_ENV == 'development' || APPLICATION_ENV == 'local'){
	            echo \Application\Exception::log($e,true);
	            $result['error'][] = $e->getMessage();
	        }else{
	            $result['error'][] = "导入excel错误"; 
	        }
	    }
        return $result;	    
	    
	}
	
	public function initData(){
		$result = array();
		$result['error'] = array();
		$tableArr = array('wf_tag','wf_ac_cluster','wf_audit','wf_ap_group',
				'wf_ac','wf_site','wf_ap','wf_bas',
				'wf_app_resource'
		);
		
		foreach ($tableArr as $table){
			$sql = "TRUNCATE TABLE `$table`;";
			$this->query($sql);
		}
		$this->beginTransaction();
		try {
			
			//tag
			$tag = new WfTag();
			$sql = "select * from sites_tag where deleted = 0;";
			$tagAll = $this->fetchAll($sql);
			$tagCount = count($tagAll);
			foreach ($tagAll as $key => $value){	
				$data = array();
				$data['tag_id'] = $value['tag_id'];
				$data['name'] = $value['name'];
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				$tag->insertRow($data);
			}
			$checkResult = $tag->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_tag where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $tagCount){
				$result['error'][] = "tag 数量不正确"; 
			}
			
			//audit
			$audit = new WfAudit();
			$sql = "SELECT * FROM `sites_audit` WHERE deleted = 0;"; 
			$auditAll = $this->fetchAll($sql);
			$auditCount = count($auditAll);
			foreach ($auditAll as $key => $value){
				$data = array();
				$data['audit_id'] = $value['audit_id'];
 				if(strtolower($value['name']) == 'tbd' || trim($value['name']) == ''){
 					$data['name'] = $data['audit_id'];
 				}else{
					$data['name'] = $value['name'];
				}
				$data['description'] = $value['description'];
				$data['producer'] = $value['producer'];
				if(strtolower($value['ip']) == 'tbd' || trim($value['ip']) == ''){
					$data['ip'] = null;
				}else{
					$data['ip'] = $value['ip'];
				}
				
				if(strtolower($value['port']) == 'tbd' || trim($value['port']) == ''){
					$data['port'] = null;
				}else{
					$data['port'] = $value['port'];
				}
				$data['encript_key'] = $value['encript_key'];
				$data['vector'] = $value['vector'];$data['user_id'] = $this->_userId;
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $audit->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$audit);
				}
				$audit->insertRow($data);
			}
			
			
			$checkResult = $audit->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_audit where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $auditCount){
				$result['error'][] = "audit 数量不正确";
			}
			
		
			//accluster
			$accluster = new WfAccluster();
			$sql = "SELECT * FROM `sites_accluster` WHERE deleted = 0;";
			$acclusterAll = $this->fetchAll($sql);
			$acclusterCount = count($acclusterAll);
			foreach ($acclusterAll as $key => $value){
				$data = array();
				$data['ac_cluster_id'] = $value['accluster_id'];
				$data['name'] = $value['name'];
				if(trim($data['name']) == ''){
					$data['name'] = $value['accluster_id'];
				}
				if(trim($value['ip']) === null || trim($value['ip']) == ''){
					$data['ip'] = null;
				}else{
					$data['ip'] = $value['ip'];
				}
				$data['description'] = $value['description'];
				if($value['type'] !== ''){
					if(strtolower(trim($value['type'])) == 'ha'){
						$value['type'] = 'HA';
					}else if(strtolower(trim($value['type'])) == 'alone'){
						$value['type'] = 'Alone';
					}else{
						$value['type'] = 'N+1';
					}
				}
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $accluster->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$accluster);
				}
				$accluster->insertRow($data);
			}
			
			$checkResult = $accluster->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_ac_cluster where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $acclusterCount){
				$result['error'][] = "ac_cluster 数量不正确";
			}
			
			//apgroup
			$apgroup = new WfApgroup();
			$sql = "SELECT * FROM `sites_apgroup` WHERE deleted = 0;";
			$apgroupAll = $this->fetchAll($sql);
			$apgroupCount = count($apgroupAll);
			foreach ($apgroupAll as $key => $value){
				$data = array();
				$data['ap_group_id'] = $value['apgroup_id'];
				$data['name'] = $value['name'];
				if(trim($data['name']) == ''){
					$data['name'] = $value['apgroup_id'];
				}
				$data['description'] = $value['description'];
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;

				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $apgroup->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$apgroup);
				}
				$apgroup->insertRow($data);
			}
			
			$checkResult = $apgroup->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_ap_group where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $apgroupCount){
				$result['error'][] = "ap_group 数量不正确";
			}
			
			//ac
			$ac = new WfAc();
			$sql = "SELECT * FROM `sites_ac` WHERE deleted = 0 order by date_entered asc;";
			$acAll = $this->fetchAll($sql);
			$acCount = count($acAll);
			foreach ($acAll as $key => $value){
				$data = array();
				$data['ac_id'] = $value['ac_id'];
				$data['name'] = $value['name'];
				$data['address'] = $value['address'];
				if($value['warrantytime'] == '1970-01-01'){
					$data['warranty_time'] = null;
				}else{
					$data['warranty_time'] = $value['warrantytime'];
				}
				$data['type'] = $value['type'];
				$data['description'] = $value['description'];
				$data['ac_mip'] = $value['mip'];
				
				$where = array();
				$where['ac_mip'] = $data['ac_mip'];
				$where['project_id'] = $this->_projectId;
				$row = $ac->fetchRow($where);
				if($row){
					$rowArray = $this->objToArray($row);
					$rowArray['ac_mip'] = '';
					$ac->updateRowById($rowArray, $row['id']);
				}
				$data['serial'] = $value['serial'];
				if(trim($value['producer']) == '' || trim($value['producer']) == null){
					$data['producer'] = '未知';
				}else{
					$data['producer'] = $value['producer'];
				}
				$data['ac_cluster_id'] = 0;
				//判断ac所在的ac组
				$sql = "select * from `sites_accluster_sites_ac_c` where sites_accluster_sites_acsites_ac_idb = '".$value['id']."'";
				$row = $this->fetchRow($sql);
				if($row){
					$sql = "SELECT accluster_id FROM `sites_accluster` WHERE id = '".$row['sites_accluster_sites_acsites_accluster_ida']."'";
					$row = $this->fetchRow($sql);
					if($row){
						//获取ac组id
						$sql = "select id from wf_ac_cluster where ac_cluster_id = '".$row['accluster_id']."'";
						$row = $this->fetchRow($sql);
						if($row){
							$data['ac_cluster_id'] = $row['id'];
						}
					}
				}
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				
				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $ac->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$ac);
				}
				$ac->insertRow($data);
			}
			
			$checkResult = $ac->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_ac where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $acCount){
				$result['error'][] = "ac 数量不正确";
			}
			
			
			//site
			$wfSite = new WfSite();
			$audit = new WfAudit();
			$tag = new WfTag();
			$siteTag = new WfSiteTag();
			$distinct = new WfDistrict();
			$distinctList = $distinct->getList();
			$distinctArr = array();
			foreach ($distinctList as $value){
				$distinctArr[$value['name']] = $value['id'];
			}
			$areaArray = array('baoshan'=>'宝山区','changning'=>'长宁区','chongming'=>'崇明县','fengxian'=>'奉贤区',
						'hongkou'=>'虹口区','huangpu'=>'黄浦区','jiading'=>'嘉定区','jingan'=>'静安区',
						'jinshan'=>'金山区','minhang'=>'闵行区','pudong'=>'浦东区','putuo'=>'普陀区','luwan'=>'卢湾区',
						'qingpu'=>'青浦区','songjiang'=>'松江区','xuhui'=>'徐汇区','yangpu'=>'杨浦区','zhabei'=>'闸北区',
			);
			$sql = "TRUNCATE TABLE `wf_site_tag`;";
			$this->query($sql);
			$sql = "SELECT * FROM `sites_site` WHERE deleted = 0 order by date_entered desc;";
			$siteAll = $this->fetchAll($sql);
			$siteCount = count($siteAll);
			foreach ($siteAll as $key => $value){
				$data = array();
				$data['name'] = $value['name'];
				$data['site_id'] = $value['site_id'];
				$data['description'] = $value['description'];
				if($value['address'] == null){
					$data['address'] = '';
				}else{
					$data['address'] = $value['address'];
				}
				
				$data['gps_e'] = $value['gps_e'];
				$data['gps_n'] = $value['gps_n'];
				$data['maintenancer'] = $value['maintenancer'];
				$data['session_timeout'] = $value['session_timeout'];
				$data['idle_timeout'] = $value['idle_timeout'];
				$data['grace_period'] = $value['grace_period'];
				$data['cover_range'] = $value['cover_range'];
				$data['network_user'] = $value['user_id1_c'];
				$data['project_user'] = $value['user_id_c'];
				$data['is_customized'] = '未知';
				if($value['if_customized'] == 0){
					$data['is_customized'] = '否';
				}
				if($value['if_customized'] == 1){
					$data['is_customized'] = '是';
				}
				
				$data['is_private'] = '未知';
				if($value['if_private'] == 0){
					$data['is_private'] = '否';
				}
				if($value['if_private'] == 1){
					$data['is_private'] = '是';
				}
				
				$data['is_mac_auth'] = '未知';
				if($value['is_mac_auth'] == 0){
					$data['is_mac_auth'] = '否';
				}
				if($value['is_mac_auth'] == 1){
					$data['is_mac_auth'] = '是';
				}
				//$data['op_url'] = $value['op_url'];
				$data['operator'] = '其他';
				
				if($value['operator'] == 'All'){
					$data['operator'] = '全部';
				}
				
				if($value['operator'] == 'unicom'){
					$data['operator'] = '联通';
				}
				
				if($value['operator'] == 'telecom'){
					$data['operator'] = '电信';
				}
				
				if($value['operator'] == 'mobile'){
					$data['operator'] = '移动';
				}
				
				$data['district_id'] = 0;
				if(strtolower($value['district']) != 'unknown' && $value['district'] != '' && $value['district'] != null){
					if($areaArray[$value['district']]){
						if($distinctArr[$areaArray[$value['district']]]){
							$data['district_id'] = $distinctArr[$areaArray[$value['district']]];
						}
					}
				}
				
				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $wfSite->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$wfSite);
				}
				
				$data['audit_id'] = 0;
				$sql = "SELECT * FROM `sites_audit_sites_site_c` WHERE deleted = 0 and sites_audit_sites_sitesites_site_idb = '".$value['id']."';";
				$row = $this->fetchRow($sql);
				if($row){
					$sql = "select audit_id from sites_audit where id = '".$row['sites_audit_sites_sitesites_audit_ida']."'";
					$row = $this->fetchRow($sql);
					if($row){
						$where = array();
						$where['audit_id'] = trim($row['audit_id']);
						$where['project_id'] = $this->_projectId;
						$row = $audit->fetchRow($where);
						if($row){
							$data['audit_id'] = $row['id'];
						}
					}
				}
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;

				$row = $wfSite->insertRow($data);
				if($row){
					$wfSiteId = $row;
					$sql = "select * from sites_tag_sites_site_c where deleted = 0 and sites_tag_sites_sitesites_tag_ida != '' and sites_tag_sites_sitesites_site_idb = '".$value['id']."'";
					$rowList = $this->fetchAll($sql);
					if($rowList){
						foreach ($rowList as $rowTmp){
							$sql = "select * from sites_tag where deleted = 0 and id = '".$rowTmp['sites_tag_sites_sitesites_tag_ida']."';";
							$row = $this->fetchRow($sql);
							if($row){
								$where = array();
								$where['tag_id'] = $row['tag_id'];
								$where['project_id'] = $this->_projectId;
								$row = $tag->fetchRow($where);
								if($row){
									$siteTagData = array();
									$siteTagData['tag_id'] = $row['id'];
									$siteTagData['site_id'] = $wfSiteId;
									$siteTag->insertRow($siteTagData);
								}
							}
						}
					}
				}
			}
			
			$checkResult = $wfSite->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_site where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $siteCount){
				$result['error'][] = "site 数量不正确";
			}
			
			
			
			//ap
			$apgroup = new WfApgroup();
			$sql = "SELECT * FROM `sites_ap` WHERE deleted = 0 order by date_modified desc;";
			$apAll = $this->fetchAll($sql);
			$apCount = count($apAll);
			$ap = new WfAp();
			$ac = new WfAc();
			$wfSite = new WfSite();
			$accluster = new WfAccluster();
			foreach ($apAll as $key => $value){
				$data = array();
				$data['ap_id'] = $value['ap_id'];
				$data['name'] = $value['name'];
				$where = array();
				$where['is_delete'] = 0;
				$where['ap_id'] = $data['ap_id'];
				$where['project_id'] = $this->_projectId;
				$row = $ap->fetchRow($where);
				if($row){
					continue;
				}
				
				if($value['serial'] == '缺失'){
					$data['serial'] = null;
				}else{
					$data['serial'] = $value['serial'];
					$where = array();
					$where['is_delete'] = 0;
					$where['serial'] = $data['serial'];
					$where['project_id'] = $this->_projectId;
					$row = $ap->fetchRow($where);
					if($row){
						continue;
					}
					
				}
				
				$data['mac_address'] = $value['mac'];
				$where = array();
				$where['is_delete'] = 0;
				$where['mac_address'] = $data['mac_address'];
				$where['project_id'] = $this->_projectId;
				$row = $ap->fetchRow($where);
				if($row){
					continue;
				}
				
				$where = array();
				$where['is_delete'] = 0;
				$where['name'] = $data['name'];
				$where['project_id'] = $this->_projectId;
				$row = $ap->fetchRow($where);
				if($row){
					$data['name'] = $this->getUniqueName($data['name'],$ap);
				}
				
				$data['gps_e'] = $value['gps_e'];
				$data['gps_n'] = $value['gps_n'];
				$data['description'] = $value['description'];
				//if($value['producer'] == null || $value['producer'] == ''){
					//$data['producer'] = $value['未知'];
				//}else{
					$data['producer'] = $value['producer'];
				//}
				
				$data['type'] = $value['type'];
				
				
				if($value['warrantytime'] == '1970-01-01'){
					$data['warranty_time'] = null;
				}else{
					$data['warranty_time'] = $value['warrantytime'];
				}
				$data['ip'] = $value['ip'];
				
				$data['ap_group_id'] = 0;
				$sql = "select * from `sites_apgroup_sites_ap_c` where deleted = 0 and sites_apgroup_sites_apsites_ap_idb = '".$value['id']."'";
				$list = $this->fetchAll($sql);
				if(count($list) == 1){
					$sql = "select * from `sites_apgroup` where deleted = 0 and id = '".$list[0]['sites_apgroup_sites_apsites_apgroup_ida']."'";
					$row = $this->fetchRow($sql);
					if($row){
						$where = array();
						$where['ap_group_id'] = $row['apgroup_id'];
						$where['project_id'] = $this->_projectId;
						$row = $apgroup->fetchRow($where);
						if($row){
							$data['ap_group_id'] = $row['id'];
						}
					}
				}
				
				$data['site_id'] = 0;
				$sql = "select * from `sites_site_sites_ap_c` where deleted = 0 and sites_site_sites_apsites_ap_idb = '".$value['id']."'";
				$list = $this->fetchAll($sql);
				if(count($list) == 1){
					$sql = "select * from `sites_site` where deleted = 0 and id = '".$list[0]['sites_site_sites_apsites_site_ida']."'";
					$row = $this->fetchRow($sql);
					if($row){
						$where = array();
						$where['site_id'] = $row['site_id'];
						$where['project_id'] = $this->_projectId;
						$row = $wfSite->fetchRow($where);
						if($row){
							$data['site_id'] = $row['id'];
						}
					}
				}
				
				$data['ac_cluster_id'] = 0;
				$sql = "select * from `sites_accluster_sites_ap_c` where deleted = 0 and sites_accluster_sites_apsites_ap_idb = '".$value['id']."'";
				$list = $this->fetchAll($sql);
				if(count($list) == 1){
					$sql = "select * from `sites_accluster` where deleted = 0 and id = '".$list[0]['sites_accluster_sites_apsites_accluster_ida']."'";
					$row = $this->fetchRow($sql);
					if($row){
						$where = array();
						$where['ac_cluster_id'] = $row['accluster_id'];
						$where['project_id'] = $this->_projectId;
						$row = $accluster->fetchRow($where);
						if($row){
							$data['ac_cluster_id'] = $row['id'];
						}
					}
				}
				
				$data['first_ac_id'] = 0;
				$sql = "select * from `sites_ac` where deleted = 0 and id = '".$value['sites_ac_id_c']."'";
				$row = $this->fetchRow($sql);
				if($row){
					$where = array();
					$where['ac_id'] = $row['ac_id'];
					$where['project_id'] = $this->_projectId;
					$row = $ac->fetchRow($where);
					if($row){
						$data['first_ac_id'] = $row['id'];
					}
				}
				
				$sql = "select * from `sites_ac` where deleted = 0 and id = '".$value['sites_ac_id1_c']."'";
				$row = $this->fetchRow($sql);
				if($row){
					$where = array();
					$where['ac_id'] = $row['ac_id'];
					$where['project_id'] = $this->_projectId;
					$row = $ac->fetchRow($where);
					if($row){
						$data['second_ac_id'] = $row['id'];
					}
				}
				
				$sql = "select * from `sites_ac` where deleted = 0 and id = '".$value['sites_ac_id2_c']."'";
				$row = $this->fetchRow($sql);
				if($row){
					$where = array();
					$where['ac_id'] = $row['ac_id'];
					$where['project_id'] = $this->_projectId;
					$row = $ac->fetchRow($where);
					if($row){
						$data['third_ac_id'] = $row['id'];
					}
				}
				
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				$ap->insertRow($data);
			}
			
			$checkResult = $ap->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_ap where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $apCount){
				//$result['error'][] = "ap 数量不正确";
			}
			
			
			//portal
// 			$portal = new WfPortal();
// 			$sql = "select * from sites_portal where deleted = 0;";
// 			$portalAll = $this->fetchAll($sql);
// 			$portalCount = count($portalAll);
// 			foreach ($portalAll as $key => $value){
// 				$data = array();
// 				$data['portal_id'] = $value['portal_id'];
// 				$data['name'] = $value['name'];
// 				$data['url'] = $value['url'];
				
// 				$where = array();
// 				$where['is_delete'] = 0;
// 				$where['name'] = $data['name'];
// 				$where['project_id'] = $this->_projectId;
// 				$row = $ac->fetchRow($where);
// 				if($row){
// 					$data['name'] = $data['name']."(".($key + 1).")";
// 				}
				
				
// 				$data['description'] = $value['description'];
// 				$data['user_id'] = $this->_userId;
// 				$data['project_id'] = $this->_projectId;
// 				$portal->insertRow($data);
// 			}
			
// 			$where = array();
// 			$where['is_delete'] = 0;
// 			$list = $portal->getList($where);
// 			foreach ($list as $key => $value){
// 				if(preg_match("/\((\d)\)/", substr($value['name'], -3),$match)){
// 					if($match[1] > 1){
// 						$str = "";
// 						for($i = 1; $i <= $match[1]; $i++){
// 							$str.= "($i)";
// 						}
// 						$value['name'] = str_replace($str, "($match[1])", $value['name']);
// 						$portal->updateRowById($value, $value['id']);
// 					}
// 				}
// 			}
			
// 			$checkResult = $portal->checkInitData();
// 			Util::getError($result, $checkResult);
// 			$sql = "select count(id) from wf_portal where project_id=".$this->_projectId;
// 			$count = (int)$this->fetchOne($sql);
// 			if($count !== $portalCount){
// 				$result['error'][] = "portal 数量不正确";
// 			}
			
			
			//bas
			$bas = new WfBas();
			$ac = new WfAc();
			$sql = "select * from sites_bas where deleted = 0;";
			$basAll = $this->fetchAll($sql);
			$basCount = count($basAll);
			foreach ($basAll as $key => $value){
				$data = array();
				$data['bas_id'] = $value['bas_id'];
				$data['name'] = $value['name'];
				$data['nas_ip'] = $value['nas_ip'];
				$data['ip_pool'] = $value['ip_pool'];				
				$data['description'] = $value['description'];
				$data['user_id'] = $this->_userId;
				$data['project_id'] = $this->_projectId;
				$bas->insertRow($data);
			}
			
			
			$checkResult = $bas->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_bas where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $basCount){
				$result['error'][] = "bas 数量不正确";
			}
			
			
			//app_resource
			$wfSite = new WfSite();
			$siteAppResource = new WfSiteAppresource();
			$sql = "TRUNCATE TABLE `wf_site_app_resource`;";
			$this->query($sql);
			$appResrouce = new WfAppresource();
			$sql = "select * from sites_app_resource where deleted = 0;";
			$appResrouceAll = $this->fetchAll($sql);
			$appResrouceCount = count($appResrouceAll);
			foreach ($appResrouceAll as $key => $value){
				$data = array();
				$data['name'] = $value['name'];
				$data['open_time'] = $value['open_time'];
				$data['category'] = $value['category'];
				$data['max_online'] = $value['max_online'];
				$data['phone'] = $value['phone'];
				$data['brand'] = $value['brand'];
				$data['address'] = $value['address'];
				$data['display_level'] = $value['display_level'];
				$data['floor'] = $value['floor'];
				$data['coordinate_x'] = $value['coordinate_x'];
				$data['coordinate_y'] = $value['coordinate_y'];
				$data['altitude'] = $value['altitude'];
				$data['gps_precision'] = $value['gps_precision'];
				$data['network_speed'] = $value['network_speed'];
				$data['business_time'] = $value['business_time'];
				$data['cover_point'] = $value['cover_point'];
				$data['cover_range'] = $value['cover_range'];
				$data['district_id'] = $value['district_id'];
				$data['description'] = $value['description'];
 				$data['user_id'] = $this->_userId;
 				$data['project_id'] = $this->_projectId;
 				
 				$where = array();
 				$where['is_delete'] = 0;
 				$where['name'] = $data['name'];
 				$where['project_id'] = $this->_projectId;
 				$row = $appResrouce->fetchRow($where);
 				if($row){
 					$data['name'] = $this->getUniqueName($data['name'],$appResrouce);
 				}
 				
 				$row = $appResrouce->insertRow($data);
 				
				if($row){
					$appResrouceId = $row;
					$sql = "select * from sites_app_resource_sites_site_c where deleted = 0 and sites_app_resource_sites_sitesites_site_idb != '' and sites_app_resource_sites_sitesites_app_resource_ida = '".$value['id']."'";
					$rowList = $this->fetchAll($sql);
					if($rowList){
						foreach ($rowList as $rowTmp){
							$sql = "select * from sites_site where deleted = 0 and id = '".$rowTmp['sites_app_resource_sites_sitesites_site_idb']."';";
 							$row = $this->fetchRow($sql);
 							if($row){
 								$where = array();
 								$where['site_id'] = $row['site_id'];
 								$where['project_id'] = $this->_projectId;
 								$row = $wfSite->fetchRow($where);
								if($row){
									$siteAppResourceData = array();
									$siteAppResourceData['app_resource_id'] = $appResrouceId;
									$siteAppResourceData['site_id'] = $row['id'];
									$siteAppResource->insertRow($siteAppResourceData);
								}
 							}
						}
					}
				}
				
			}
			
			$checkResult = $appResrouce->checkInitData();
			Util::getError($result, $checkResult);
			$sql = "select count(id) from wf_app_resource where project_id=".$this->_projectId;
			$count = (int)$this->fetchOne($sql);
			if($count !== $appResrouceCount){
				$result['error'][] = "app_resource 数量不正确";
			}
			
			if(!$result['error']){
				$this->commit();
			}else{
				$this->rollback();
			}
			
		}catch (\Exception $e) {
			$this->rollback();
			\Application\Exception::log($e);
			echo \Application\Exception::log($e,true);
		}
		return $result;
		
	}
	
	public function insertImport($data){
	    
	    unset($data['id']);
	    unset($data['submit']);
	    unset($data['cancel']);
	    $this->insert($data);
	    return $this->tableGateway->lastInsertValue;
	}
	
	public function getImportById($id)
	{
	    $result = $this->fetchRow(array('id'=> $id));
	    return $result;
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
	    $this->_select->order(array('id DESC'));
	    
	    $adapter = new DbSelect ($this->_select, $sql);
	    $paginator = new Paginator ( $adapter );
	    
	    return $paginator;
	    
	}
	
	
	public function getImportByName($name)
	{
	    $result = $this->fetchRow(array('name'=> $name,'project_id'=>$this->_projectId));
	    return $result;
	}
	
	public function getForm($data = array(),$importId=null){
	    $import = new WfImport();
	    $form = new Form();
	    $form->setAttribute('action', '/import/index')
	    ->setAttribute('class', 'form-horizontal')
	    ->setAttribute('method', 'post')
	    ->setAttribute('id', 'import_form')
	    ->setAttribute('enctype', 'multipart/form-data');
	    
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
	            'onclick' => 'window.location=\'/import/list\';',
	        ),
	    ));

	    $form->add(
	        array(
	            'name' => 'id',
	            'type' => 'Hidden',
	            'attributes' => array(
	                'value' => $importId,
	                'id'    => 'id',
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
	    
	    $inputFilter = new InputFilter();
	    $factory     = new Factory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'allowEmpty' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            


        )));

	    $form->setInputFilter($inputFilter);
	    
	    //set data
	    if (is_array ($data)) {
	        $form->setData($data);
	    }
	    return $form;
	    
	}
	
	public function getUniqueName($name,DbTable $class){
		$where = new Where();
		$table = $class->tableGateway->getTable();
		$sql = "select * from ".$table." where `name` like '".$name."(%' order by id desc";
		$list = $this->fetchAll($sql);
		if($list){
			$name = $list[0]['name'];
			if(preg_match("/\((\d)\)/", substr($list[0]['name'], -3),$match)){
				if($match[1]){
					$count = $match[1] + 1;
					$name = substr($name, 0,strlen($name) - 3)."(".$count.")";
				}
			}
		}else{
			$name.= "(1)";
		}
		return $name;
	}
	
// 	public function deleteById($id){
// 	    $ret = $this->tableGateway->delete($this->quoteInto('id=?', $id));
// 	    return $ret;
// 	}
	
	
}