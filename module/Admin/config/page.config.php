<?php
use Admin\Model\Auth;
$identity = Auth::getIdentity();
$pageReturn = array(
    'default' => array(
        array(
            'label' => '导入管理',
            'module' => 'import',
            'route' => 'default',
            'controller' => 'import',
            'action' => 'list',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'import',
                    'action' => 'list',
                    'resource' => 'import_import_list',
                    'link' => '/import/list',
                ),
                array(
                    'label' => '导入excel',
                    'controller' => 'import',
                    'action'     => 'index',
                    'resource' => 'import_import_index',
                    'link' => '/import/index',
                ),
            )
        ),
        
        array(
            'label' => '场点管理',
            'module' => 'sites',
            'route' => 'default',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'sites',
                    'action' => 'list',
                    'resource' => 'sites_sites_list',
                    'link' => '/sites/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'sites',
                    'action' => 'index',
                    'resource' => 'sites_sites_index',
                    'link' => '/sites/index',
                ),
            )
        ),
        
//         array(
//             'label' => 'portal管理',
//             'module' => 'portal',
//             'route' => 'default',
//             'controller' => 'portal',
//             'action' => 'list',
//             'pages' => array(
//                 array(
//                     'label' => '列表',
//                     'route' => 'default',
//                     'controller' => 'portal',
//                     'action' => 'list',
//                     'resource' => 'portal_portal_list',
//                     'link' => '/portal/list',
//                 ),
//                 array(
//                     'label' => '添加',
//                     'route' => 'default',
//                     'controller' => 'portal',
//                     'action' => 'index',
//                     'resource' => 'portal_portal_index',
//                     'link' => '/portal/index',
//                 ),
//             )
//         ),
        
        array(
            'label' => '标签管理',
            'module' => 'tag',
            'route' => 'default',
            'controller' => 'tag',
            'action' => 'list',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'tag',
                    'action' => 'list',
                    'resource' => 'tag_tag_list',
                    'link' => '/tag/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'tag',
                    'action' => 'index',
                    'resource' => 'tag_tag_index',
                    'link' => '/tag/index',
                ),
            )
        ),
        
        array(
            'label' => 'AP组管理',
            'module' => 'apgroup',
            'route' => 'default',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'apgroup',
                    'action' => 'list',
                    'resource' => 'apgroup_apgroup_list',
                    'link' => '/apgroup/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'apgroup',
                    'action' => 'index',
                    'resource' => 'apgroup_apgroup_index',
                    'link' => '/apgroup/index',
                ),
            )
        ),
        
        array(
            'label' => 'AP管理',
            'module' => 'ap',
            'route' => 'default',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'ap',
                    'action' => 'list',
                    'resource' => 'ap_ap_list',
                    'link' => '/ap/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'apgroup',
                    'action' => 'index',
                    'resource' => 'ap_ap_index',
                    'link' => '/ap/index',
                ),
            )
        ),
        
        array(
            'label' => 'AC组管理',
            'module' => 'accluster',
            'route' => 'default',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'accluster',
                    'action' => 'list',
                    'resource' => 'accluster_accluster_list',
                    'link' => '/accluster/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'accluster',
                    'action' => 'index',
                    'resource' => 'accluster_accluster_index',
                    'link' => '/accluster/index',
                ),
            )
        ),
        
        array(
            'label' => 'AC管理',
            'module' => 'ac',
            'route' => 'default',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'ac',
                    'action' => 'list',
                    'resource' => 'ac_ac_list',
                    'link' => '/ac/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'ac',
                    'action' => 'index',
                    'resource' => 'ac_ac_index',
                    'link' => '/ac/index',
                ),
            )
        ),
        
        array(
            'label' => '审计设备管理',
            'module' => 'audit',
            'route' => 'default',
            'controller' => 'audit',
            'action' => 'list',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'audit',
                    'action' => 'list',
                    'resource' => 'audit_audit_list',
                    'link' => '/audit/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'audit',
                    'action' => 'index',
                    'resource' => 'audit_audit_index',
                    'link' => '/audit/index',
                ),
            )
        ),
        
        array(
            'label' => 'bas管理',
            'module' => 'bas',
            'route' => 'default',
            'controller' => 'bas',
            'action' => 'list',
            'pages' => array(
                array(
                    'label' => '列表',
                    'route' => 'default',
                    'controller' => 'bas',
                    'action' => 'list',
                    'resource' => 'bas_bas_list',
                    'link' => '/bas/list',
                ),
                array(
                    'label' => '添加',
                    'route' => 'default',
                    'controller' => 'bas',
                    'action' => 'index',
                    'resource' => 'bas_bas_index',
                    'link' => '/bas/index',
                ),
            )
        ),
    	array(
			'label' => 'App资源管理',
    		'module' => 'appresource',
    		'route' => 'default',
    		'controller' => 'appresource',
    		'action' => 'list',
    		'pages' => array(
    			array(
    				'label' => '列表',
    				'route' => 'default',
    				'controller' => 'bas',
    				'action' => 'list',
    				'resource' => 'appresource_appresource_list',
    				'link' => '/appresource/list',
    			),
    			array(
    				'label' => '添加',
    				'route' => 'default',
    				'controller' => 'appresource',
    				'action' => 'index',
    				'resource' => 'appresource_appresource_index',
    				'link' => '/appresource/index',
    			),
    		)
    	),
    ),
);
if($identity['parent_id'] == 1){
    $aclArrayTmp = array(
        'label' => '用户管理',
        'module' => 'acl',
        'route' => 'default',
        'controller' => 'acl',
        'action' => 'user-list',
        'resource' => 'acl_user-list',
        'link' => '/acl/user-list',
//         'pages' => array(
//             array(
//                 'label' => '用户',
//                 'route' => 'default',
//                 'controller' => 'acl',
//                 'action' => 'user-list',
//                 'resource' => 'acl_user-list',
//                 'link' => '/acl/user-list',
//             ),
//         ),
        
//         array(
//             'label' => 'Site',
//             'route' => 'default',
//             'module' => 'admin',
//             'controller' => 'site',
//             'action' => 'index',
//             'resource' => 'admin_site_index',
//         ),
    );
    array_unshift($pageReturn['default'],$aclArrayTmp);
}
if($identity['parent_id'] == 0){
    $pageReturn = array(
        'default' => array(
            array(
                'label' => '用户/权限',
                'module' => 'acl',
                'route' => 'default',
                'controller' => 'acl',
                'pages' => array(
                    array(
                        'label' => 'Module',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'module-list',
                        'resource' => 'acl_module-list',
                        'link' => '/acl/module-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Module',
                                'module' => 'acl',
                                'route' => 'default',
                                'controller' => 'acl',
                                'action'     => 'module-edit',
                                'resource' => 'acl_module-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => 'Controller',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'controller-list',
                        'resource' => 'acl_controller-list',
                        'link' => '/acl/controller-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Controller',
                                'controller' => 'acl',
                                'action'     => 'controller-edit',
                                'resource' => 'acl_controller-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => 'Action',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'action-list',
                        'resource' => 'acl_action-list',
                        'link' => '/acl/action-list',
                        'pages' => array(
                            array(
                                'label' => 'Add Action',
                                'controller' => 'acl',
                                'action'     => 'action-edit',
                                'resource' => 'acl_action-edit',
                            ),
                        )
                    ),
                    array(
                        'label' => '用户',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'user-list',
                        'resource' => 'acl_user-list',
                        'link' => '/acl/user-list',
                    ),
                    array(
                        'label' => '角色/权限',
                        'route' => 'default',
                        'controller' => 'acl',
                        'action' => 'role-list',
                        'resource' => 'acl_role-list',
                        'link' => '/acl/role-list',
                    ),
                ),
            ),
            
            array(
                'label' => '项目管理',
                'module' => 'project',
                'route' => 'default',
                'controller' => 'project',
                'action' => 'list',
                'pages' => array(
                    array(
                        'label' => '列表',
                        'route' => 'default',
                        'controller' => 'project',
                        'action' => 'list',
                        'resource' => 'project_project_list',
                        'link' => '/project/list',
                    ),
                    array(
                        'label' => '添加',
                        'controller' => 'project',
                        'action'     => 'index',
                        'resource' => 'project_project_index',
                        'link' => '/project/index',
                    ),
                )
            ),
        ),
    );
}

return $pageReturn;
