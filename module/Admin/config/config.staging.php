<?php

$configProduction = require ROOT_PATH . '/module/Admin/config/config.production.php';

$config =  array (
 		'cmsHost' => 'staging.siteproject.com',
);

return array_merge($configProduction,$config);
