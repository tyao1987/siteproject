<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Test\Data;

use Admin\Model\Auth;
use Admin\Model\User;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        if($this->_user['id'] == 1){
            return $this->redirect()->toUrl('/project/list');
        }
        if($this->_user['parent_id'] == 1){
            return $this->redirect()->toUrl('/acl/user-list');
        }
        return $this->redirect()->toUrl('/import/list');
    	//return new ViewModel ();
    }
    
    public function updateMyPasswordAction()
    {
        if ($this->request->isPost()) {
            $user = new User();
            $data = $this->params()->fromPost();
            $oldPassword = $data['old_password'];
            $identity = Auth::getIdentity();
            $userInfo = $user->getUserById($identity['id']);
            if(md5($oldPassword) != $userInfo['password']){
                $viewData = array ();
                $viewData['error']['save'] = "原密码错误";
                return new ViewModel ($viewData);
            }else{
                $user->updateMyPassword($data);
                Auth::destroy();
                return  $this->redirect()->toRoute('default', array('controller'=> 'auth',"action"=>"login"));
            }
            //$logMessage = "修改密码";
            //$this->saveLog($logMessage,$this->objToArray($userInfo));
            
           
        }
    }
    
}
