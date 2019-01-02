<?php

$configProduction = require ROOT_PATH . '/module/Admin/config/config.production.php';

$config =  array (
    'cmsHost' => 'www.siteproject.com',
	'siteImagePath' => array(1 => '/var/www/site_image/aishanghai',2 => '/var/www/site_image/aichongqing'),
);

return array_merge($configProduction,$config);
