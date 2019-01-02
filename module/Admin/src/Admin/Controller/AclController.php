<?php
namespace Admin\Controller;

use Admin\Model\Action;
use Admin\Model\Controller;
use Admin\Model\DealCache;
use Admin\Model\Module;
use Admin\Model\Role;
use Admin\Model\RoleAction;
use Admin\Model\SiteGroup;
use Admin\Model\Sites;
use Admin\Model\User;


use Zend\Form\Form;


use Admin\Util\Post;
use Zend\View\Model\ViewModel;


class AclController extends AbstractController {

    public function checkUserParent($userId){
        if($this->_user['id'] != 1 && $this->_user['id'] != $userId){
            $userModel = new User();
            $user = $userModel->getUserById($userId);
            if(!$user || $user['parent_id'] != $this->_user['id']){
                return $this->redirect()->toRoute('default', array(
                    'controller'=> 'acl',
                    'action'    => 'user-list',
                ));
            }
        }
    }
    
    public function checkAdmin(){
        if($this->_user['id'] != 1){
            return $this->redirect()->toRoute('default', array(
                'controller'=> 'acl',
                'action'    => 'user-list',
            ));
        }
    }
    
	public function indexAction(){
	    $this->checkAdmin();
		return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'module-list',
				));
	}

	public function moduleListAction(){
	    $this->checkAdmin();
		$param = $this->params()->fromQuery();

		$module = new Module();
		$paginator = $module->paginator($param);
		$paginator->setCurrentPageNumber((int)$param['page']);
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ( $param['perpage'] );

		$viewData ['paginator'] = $paginator;
		$viewData = array_merge ( $viewData, $param);

		return new ViewModel ($viewData);

	}

	public function controllerListAction(){
	    $this->checkAdmin();
		$param = $this->params()->fromQuery();

		$controller = new Controller();
		$paginator = $controller->paginator ( $param );
		$paginator->setCurrentPageNumber (( int )$param ['page']);
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ($param['perpage']);
		

		$module = new Module();
		$modules = $module->getModulesPairs();

		$viewData['modules'] = $modules;
		$viewData ['paginator'] = $paginator;
		$viewData = array_merge ($viewData, $param);

		return new ViewModel ($viewData);

	}

	public function actionListAction(){
	    $this->checkAdmin();
		$param = $this->params()->fromQuery();

		$clause = array();

		$controllerId = (int)$this->params()->fromQuery('controller_id', 0);
		if ($controllerId) {
			$clause['controller_id'] = $controllerId;
		}

		$actionName = (string)$this->params()->fromQuery('action_name', '');
		if ($actionName) {
			$clause['name'] = $actionName;
		}

		$action = new Action();
		$paginator = $action->paginator($clause);
		$paginator->setCurrentPageNumber ((int)$param['page']);
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ( $param['perpage'] );
		

		$Controller = new Controller();
		$controllers = $Controller->getControllersPairs();
		natsort($controllers);
		$viewData['controllers'] = $controllers;
		$viewData ['paginator'] = $paginator;
		$viewData = array_merge ( $viewData, $param);

		return new ViewModel ( $viewData );

	}


	public function roleListAction(){
	    $this->checkAdmin();
		$param = $this->params ()->fromQuery ();

		$role = new Role();
		$paginator = $role->paginator ();
		$paginator->setCurrentPageNumber ( ( int ) $param ['page'] );
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ( $param['perpage'] );
		

		$viewData ['paginator'] = $paginator;
		$viewData = array_merge ( $viewData, $param);

		return new ViewModel ( $viewData );

	}

	public function siteGroupListAction(){
	    $this->checkAdmin();
		$param = $this->params ()->fromQuery ();

		$siteGroup = new SiteGroup();
		$paginator = $siteGroup->paginator ( array('name'=>$param['name']) );
		$paginator->setCurrentPageNumber ( ( int ) $param ['page'] );
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ( $param['perpage'] );
		

		$viewData ['paginator'] = $paginator;
		$viewData = array_merge ( $viewData, $param);

		return new ViewModel ( $viewData );

	}

	public function userListAction(){
		$param = $this->params()->fromQuery();
		$user = new User();
		if(!isset($param['is_delete'])){
		    $param['is_delete'] = 0;
		}
		
		$paginator = $user->paginator($param);
		$paginator->setCurrentPageNumber ((int) $param ['page'] );
		if(empty($param['perpage'])){
			$param['perpage'] = 20; 
		}
		$paginator->setItemCountPerPage ( $param['perpage'] );
		

		$viewData ['paginator'] = $paginator;
		$viewData ['param'] = $param;
		$viewData = array_merge ($viewData, $param);

		return new ViewModel ( $viewData );

	}

	public function moduleEditAction() {
	    $this->checkAdmin();
		$module = new Module();
		$form = $module->getAclModuleForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {
			$data = $form->getData();

			unset($data['submit']);
			unset($data['cancel']);

			$id = (int)$data['id'];
			if ($id) {
				$module->updateModule($id, $data);
				$logMessage = "修改Module id:".$id;
				//$this->saveLog($logMessage,$this->objToArray($data));
			} else {
				$insertId = $module->insertModule($data);
				$logMessage = "新建Module id:".$insertId;
				$data['id'] = $insertId;
				//$this->saveLog($logMessage,$this->objToArray($data));
			}

			$this->_clearResources();
			return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'module-list',
				));
		}


		$id = ( int ) $this->params()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $module->getModuleById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'module-list',
				));
			}
			$form->setData( $data);
			$form->get('submit')->setValue('编辑');
		}
		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );

	}

	public function controllerEditAction() {
	    $this->checkAdmin();
		$controller = new Controller();
		$form = $controller->getAclControllerForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {
			$data = $form->getData();

			unset($data['submit']);
			unset($data['cancel']);

			$id = (int)$data['id'];
			if ($id) {
				$controller->updateController($id, $data);
				$logMessage = "修改Controller id:".$id;
				//$this->saveLog($logMessage,$this->objToArray($data));
			} else {
				$insertId = $controller->insertController($data);
				$logMessage = "新建Controller id:".$insertId;
				$data['id'] = $insertId;
				//$this->saveLog($logMessage,$this->objToArray($data));
			}

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'controller-list',
			));
		}


		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $controller->getControllerById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'controller-list',
				));
			}
			$form->setData( $data);
			$form->get('submit')->setValue('编辑');
		}
		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );

	}

	public function actionEditAction() {
	    $this->checkAdmin();
		$action = new Action();

		$form = $action->getAclActionForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {
			$data = $form->getData();

			unset($data['submit']);
			unset($data['cancel']);

			$id = (int)$data['id'];
			if ($data['controller_id']) {
			    $controller = new Controller();
				$module = $controller->getControllerById($data['controller_id']);
				$data['module_id'] = $module->module_id;
			}
			if ($id) {
				$action->updateAction($id, $data);
				$logMessage = "修改Action id:".$id;
				//$this->saveLog($logMessage,$this->objToArray($data));
			} else {
			    $insertId = $action->insertAction($data);
				$logMessage = "新建Action id:".$insertId;
				$data['id'] = $insertId;
				//$this->saveLog($logMessage,$this->objToArray($data));
			}

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'action-list',
			));
		}


		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $action->getActionById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'action-list',
				));
			}
			$form->setData( $data);
			$form->get('submit')->setValue('Edit Action');
		}
		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );

	}

	public function roleEditAction() {
	    $this->checkAdmin();
		$role = new Role();
		$form = $role->getAclRoleForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {
			$data = $form->getData();

			unset($data['submit']);
			unset($data['cancel']);

			$id = (int)$data['id'];
			if ($id) {
				$role->updateRole($id, $data);
				$logMessage = "修改角色 id:".$id;
				//$this->saveLog($logMessage,$this->objToArray($data));
			} else {
			    $insertId = $role->insertRole($data);
			    $logMessage = "添加角色 id:".$insertId;
			    $data['id'] = $insertId;
			    //$this->saveLog($logMessage,$this->objToArray($data));
			}

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'role-list',
			));
		}


		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $role->getRoleById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'role-list',
				));
			}
			$form->get('submit')->setValue('编辑');
			$form->setData( $data);
		}
		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );

	}

	public function siteGroupEditAction() {
	    $this->checkAdmin();
		$siteGroup = new SiteGroup();
		$form = $siteGroup->getAclSiteGroupForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {
			$data = $form->getData();

			unset($data['submit']);
			unset($data['cancel']);


			$id = (int)$data['id'];
			if ($id) {
				$siteGroup->updateSiteGroup($id, $data);
			} else {
				$siteGroup->insertSiteGroup($data);
			}

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'site-group-list',
			));
		}


		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $siteGroup->getSiteGroupById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'site-group-list',
				));
			}
			$form->setData( $data);
			$form->get('submit')->setValue('Edit Site Group');
		}
		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );

	}

	public function userAddAction() {
	    
		$user = new User();

		$form = $user->getAclUserForm($_POST);
		if ($this->request->isPost() && $form->isValid()) {
		    
			$data = $form->getData();
			
			$id = (int)$data['id'];
			if ($id) {
				$user->updateUser($id, $data);
			} else {
			    $data['parent_id'] = $this->_user['id'];
			    if($this->_user['id'] != 1){
			        $data['project_id'] = $this->_user['project_id'];
			    }
				$id = $user->insertUser($data);
				$data = array(1);
				$user->updateSelectedSiteGroups($data, $id);
				if($this->_user['id'] == 1){
				    $data = array(7);
				    $user->updateSelectedRoles($data, $id);
				}
				if($this->_user['parent_id'] == 1){
				    $data = array(8);
				    $user->updateSelectedRoles($data, $id);
				}
				//$logMessage = "新建用户 id:".$id;
				//$this->saveLog($logMessage,$this->objToArray($user->getUserById($id)));
			}

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'user-list',
					'id'		=> $id,
			));
		}


		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );

		// edit, then get old data
		if ($id) {
			$data = $user->getUserById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'user-list',
				));
			}
			$form->setData( $data);
		}
		$viewData = array ();
		$viewData['form'] = $form;
		$viewData['error'] = $form->getMessages();
		return new ViewModel ( $viewData );

	}

	public function moduleDeleteAction() {
	    $this->checkAdmin();
		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );
		$module = new Module();
		$module->deleteModule($id);
		$logMessage = "删除Module id:".$id;
		//$this->saveLog($logMessage);
		$refer = $_SERVER['HTTP_REFERER'];
		if ($refer) {
			return $this->redirect()->toUrl($refer);
		} else {
			return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'module-list',
				));
		}
	}

	public function controllerDeleteAction() {
	    $this->checkAdmin();
		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );
		$controller = new Controller();
		$controller->deleteController($id);
		
		$logMessage = "删除Controller id:".$id;
		//$this->saveLog($logMessage);
		$refer = $_SERVER['HTTP_REFERER'];
		if ($refer) {
			return $this->redirect()->toUrl($refer);
		} else {
			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'controller-list',
			));
		}
	}

	public function actionDeleteAction() {
	    $this->checkAdmin();
		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );
		$action = new Action();
		$action->deleteAction($id);
		$logMessage = "删除Action id:".$id;
		//$this->saveLog($logMessage);
		$refer = $_SERVER['HTTP_REFERER'];
		if ($refer) {
			return $this->redirect()->toUrl($refer);
		} else {
			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'action-list',
			));
		}
	}

	public function roleDeleteAction() {
	    $this->checkAdmin();
		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );
		$role = new Role();
		$role->deleteRole($id);
		$logMessage = "删除角色 id:".$id;
		//$this->saveLog($logMessage);
		$refer = $_SERVER['HTTP_REFERER'];
		if ($refer) {
			return $this->redirect()->toUrl($refer);
		} else {
			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'role-list',
			));
		}
	}


	public function siteGroupDeleteAction() {
	    $this->checkAdmin();
		$id = ( int ) $this->params ()->fromRoute ( "id", 0 );
		$siteGroup = new SiteGroup();
		$siteGroup->deleteSiteGroup($id);

		$refer = $_SERVER['HTTP_REFERER'];
		if ($refer) {
			return $this->redirect()->toUrl($refer);
		} else {
			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'site-group-list',
			));
		}
	}

	public function roleManageAction() {
	    $this->checkAdmin();
		$role = new Role();
		$form = $role->getAclRoleManageForm($_POST);

		if ($this->request->isPost() && $form->isValid()) {

			$data = $form->getData();

			unset($data['submit']);

// 			$selectedData = $data['selectedData'];
            $selectedData = Post::get('selectedData');
			$actions = explode(',', $selectedData);


			$roleAction = new RoleAction();

			$id = (int)$data['id'];

			$role->updateRole($id, array('name'=>$data['name'],'description'=>$data['description']));
			$roleAction->updateRoleByActions($id, $actions);
           
			$logMessage = "编辑角色信息及权限 id:".$id;
			$logData = array();
			$logData['name'] = $data['name'];
			$logData['description'] = $data['description'];
			$logData['id'] = $id;
			$logData['action'] = $_POST['selectedData'];
			//$this->saveLog($logMessage,$logData);
			
			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'role-list',
			));
		}

		$id = (int) $this->params ()->fromRoute ( "id", 0 );
		if(!$id){
			$id = ( int ) $this->params ()->fromPost ( "id", 0 ); 
		}
		// edit, then get old data
		if ($id) {
			$data = $role->getRoleById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'role-list',
				));
			}
			$form->setData( $data);

			$roleAction = new RoleAction();
			$selectedRoles = $roleAction->getSelectedActions($id);
			$unselectedRoles = $roleAction->getUnselectedActions($id);

			$form->get('leftSelector')->setValueOptions($unselectedRoles);
			$form->get('selected')->setValueOptions($selectedRoles);

		}

		$viewData = array ();
		$viewData['form'] = $form;
		return new ViewModel ( $viewData );
	}


	public function siteGroupManageAction() {
	    $this->checkAdmin();

		$siteGroup= new SiteGroup();
		$form = $siteGroup->getAclSiteGroupManageForm($_POST);
		
		$id = ( int ) $this->params ()->fromRoute ( "id", ( int ) $this->params ()->fromPost ( "id", 0 ) );
		
		if ($this->request->isPost() && $form->isValid()) {

			$data = $form->getData();

			unset($data['submit']);

			$selectedData = $data['selectedData'];
			$selected = explode(',', $selectedData);
			$selected = array_filter($selected);

			$id = (int)$data['id'];

			$siteGroup = new SiteGroup();

			$siteGroup->updateSiteGroup($id, array('name'=>$data['name'],'description'=>$data['description']));

			$siteGroup->updateRelationById($selected, $id);

			$this->_clearResources();

			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'site-group-list',
			));
		}


		// edit, then get old data
		if ($id) {

			$data = $siteGroup->getSiteGroupById($id);
			if (!$data) {
				return $this->redirect()->toRoute('default', array(
						'controller'=> 'acl',
						'action'    => 'site-group-list',
				));
			}
			$form->setData( $data);

			$site = new Sites();
			$sites = $site->getSitesPairs();

			$siteGroup = new SiteGroup();
			$selected = $siteGroup->getSelectedSitesBySiteGroupId($id);

			foreach($selected as $item => $svalue) {
				foreach($sites as $key => $value) {
					if($item == $key) {
						unset($sites[$key]);
					}
				}
			}

			$form->get('site')->setValueOptions($sites);
			$form->get('selected')->setValueOptions($selected);

		}

		$viewData = array();
		$viewData['form'] = $form;
		return new ViewModel($viewData);
	}

	public function userManageAction() {
		$user = new User();
		$viewData = array();

		$id = (int) $this->params()->fromRoute("id", (int)$this->params()->fromPost("id", 0));
		if(empty($id)){
			return $this->redirect()->toRoute('default', array(
					'controller'=> 'acl',
					'action'    => 'user-list',
			));
		}
		$this->checkUserParent($id);


		$aclUserForm = $user->getAclUserForm($_POST,$id);
		$aclUserForm->setAttribute('action', '/acl/user-manage')
					->setAttribute('name', 'form_general');
		$aclUserForm->get('submit')->setAttribute('value','修改用户信息');

		$aclUserRolesForm = $user->getAclUserRolesForm($id);
		$aclUserSiteGroupsForm = $user->getAclUserSiteGroupsForm($id);
		$aclUserSitesForm = $user->getAclUserSitesForm($id);

		if ($this->request->isPost()) {
			$redirect = true;
			if(key_exists('selectedRolesData', $_POST)){
			    $this->checkAdmin();
				$selected = explode(',', $this->params ()->fromPost('selectedRolesData'));
				$selected = array_filter($selected);
				$user->updateSelectedRoles($selected, $id);
				$data = $user->getSelectedRolesByUserId($id);
				$logMessage = "修改用户角色 用户id:".$id;
				$data = array();
				$data['user_id'] = $id;
				$data['role_id'] = $_POST['selectedRolesData'];
				//$this->saveLog($logMessage,$data);
				unset($data);
			}elseif(key_exists('selectedSitesData', $_POST)){
			    $this->checkAdmin();
				$selected = explode(',', $this->params ()->fromPost('selectedSitesData'));
				$selected = array_filter($selected);
				$user->updateSelectedSites($selected, $id);
			}elseif(key_exists('selectedSiteGroupsData', $_POST)){
			    $this->checkAdmin();
				$selected = explode(',', $this->params ()->fromPost('selectedSiteGroupsData'));
				$selected = array_filter($selected);
				$user->updateSelectedSiteGroups($selected, $id);
			}else{
				$redirect = false;
				if($aclUserForm->isValid()){
					$data = $aclUserForm->getData();
// 					if($data['update_password']=='1'){
// 						//if(empty($data['newPassword']) || empty($data['newConfirmPassword']) || $data['newPassword']!=$data['newConfirmPassword']){
// 							//$viewData['error'] = array('password'=>'密码不一致');
// 						//}
// 						//$data['password'] = md5(User::INIT_PWD);
// 						//$data['update_pwd'] = 1;
// 					}
					if(empty($viewData['error'])){
						$id = (int)$data['id'];
					    $userInfo = $user->getUserById($id);
					    if($data['name'] != $userInfo['name']){
					        $result = $user->getUserByName($data['name']);
					        if($result){
					            $url = "/acl/user-manage/id/".$id."?error=exist";
					            return $this->redirect()->toUrl($url);
					        }
					    }
						$user->updateUser($id, $data);
// 						$userInfo = $user->getUserById($id);
// 						$logMessage = "编辑用户 id:".$id;
// 						if($data['update_password']=='1'){
// 						    $logMessage = "编辑用户并重置用户密码 id:".$id;
// 						}
						//$this->saveLog($logMessage,$this->objToArray($userInfo));
					}
				}else{
					$viewData['error'] = $aclUserForm->getMessages();
				}
			}
			if(empty($viewData['error'])){
				$this->_clearResources();
			}

//			if($redirect){
//				$url = '/acl/user-manage/id/' . $id . '?scope=' . $this->params ()->fromPost('scope');
//				return $this->redirect()->toUrl($url);
//			}

			$url = '/acl/user-list';
			return $this->redirect()->toUrl($url);
		}else{

			$scope = $this->params()->fromQuery('scope', 'general');
			if ($id) {
				$data = $user->getUserById($id);
				if (!$data) {
					return $this->redirect()->toRoute('default', array(
							'controller'=> 'acl',
							'action'    => 'user-list',
					));
				}
				$aclUserForm->setData( $data);
			}
		}

		$viewData['aclUserForm'] = $aclUserForm;
		$viewData['aclUserRolesForm'] = $aclUserRolesForm;
		$viewData['aclUserSiteGroupsForm'] = $aclUserSiteGroupsForm;
		$viewData['aclUserSitesForm'] = $aclUserSitesForm;
		$viewData['scope'] = $scope;
		return new ViewModel($viewData);
	}

// 	public function userDeleteAction() {
// 		$id = (int) $this->params()->fromRoute( "id", 0 );
// 	   	if($id == 1) {
// 	   		throw new \Exception("Can't delete the system default user!");
// 	   	}
	   	
// 	   	$user = new User();
// 	   	$userInfo = $user->getUserById($id);
// 	   	if(!$userInfo){
// 	   	    throw new \Exception("User not exist");
// 	   	}
// 	   	$data = array();
// 	   	if($userInfo['is_delete'] == 0){
// 	   	    $data['is_delete'] = 1;
// 	   	}
// 	   	$user->updateUser($id, $data);
// 	   	$userInfo['is_delete'] = 1;
// 	   	$logMessage = "删除用户 id:".$id;
// 	   	//$this->saveLog($logMessage,$this->objToArray($userInfo));
// 	   	$url = "/acl/user-list";
// 	   	return $this->redirect()->toUrl($url);
	   	
// 	}

// 	public function userReactiveAction() {
// 	    $id = (int) $this->params()->fromRoute( "id", 0 );
// 	    if($id == 1) {
// 	        throw new \Exception("不能删除超级管理员");
// 	    }
	    
// 	    $user = new User();
// 	    $userInfo = $user->getUserById($id);
// 	    if(!$userInfo){
// 	        throw new \Exception("User not exist");
// 	    }
// 	    $data = array();
// 	    if($userInfo['is_delete'] == 1){
// 	        $data['is_delete'] = 0;
// 	    }
// 	    $user->updateUser($id, $data);
// 	    $userInfo['is_delete'] = 0;
// 	    $logMessage = "还原删除用户 id:".$id;
// 	    //$this->saveLog($logMessage,$this->objToArray($userInfo));
// 	    $url = "/acl/user-list";
// 	    return $this->redirect()->toUrl($url);
	    
// 	}
	
	
	public function userActiveAction() {
	    $id = (int) $this->params()->fromRoute( "id", 0 );
	    if($id == 1) {
	        throw new \Exception("Can't active the system default user!");
	    }
	    $this->checkUserParent($id);
	    
	    if($this->_user['parent_id'] == 1 && $id == $this->_user['id']){
	        return $this->redirect()->toRoute('default', array(
	            'controller'=> 'acl',
	            'action'    => 'user-list',
	        ));
	    }
	    
	    $user = new User();
	    $userInfo = $user->getUserById($id);
	    if(!$userInfo){
	        throw new \Exception("用户不存在");
	    }
	    $data = array();	    
	    if($userInfo['is_active'] == 1){
	        $data['is_active'] = 0;
	    }else{
	        $data['is_active'] = 1;
	    }
	    $user->updateUser($id, $data);
	    $userInfo['is_active'] = $data['is_active'];
	    if($data['is_active'] == 0){
	        $logMessage = "禁用用户 id:".$id;
	        //$this->saveLog($logMessage,$this->objToArray($userInfo));
	    }else{
	        $logMessage = "启用用户 id:".$id;
	        //$this->saveLog($logMessage,$this->objToArray($userInfo));
	    }
	    $url = "/acl/user-list";
	    return $this->redirect()->toUrl($url);
	}
	
// 	public function updateMyPasswordAction()
// 	{
// 	    if ($this->request->isPost()) {
// 	        $user = new User();
// 	        $data = $this->params()->fromPost();
// 	        $user->updateMyPassword($data);
// 	        $url = '/';
// 	        return $this->redirect()->toUrl($url);
// 	    }
// 	}
	
	protected function _clearResources(){
		//更新缓存文件
		$dealCache = new DealCache() ;
		$dealCache->dealAclResources() ;
	}

}