<?php
return array(
    'defaultSiteId' => 1,
	'cmsHost' => 'admin.juneyaokc.com',
    'cmsWritableDir' => array (
			'base'            => '',
            'javascript'      => ROOT_PATH . '/public/scripts/',
            'images'          => ROOT_PATH . '/public/images/',
            'dataCache'       => ROOT_PATH . '/data/data-cache/',
		),
	'cmsDefaultTimezone' => 'Asia/Shanghai',
    'siteImagePath' => array(1 => '/data/site_image/aishanghai',2 => '/data/site_image/aichongqing'),
    'siteSSID' => array(1 => 'i-Shanghai',2 => 'i-Chongqing'),
    'siteImageServer'  =>  array(
        1 => 'ash',
        2 => 'acq',
    )
);