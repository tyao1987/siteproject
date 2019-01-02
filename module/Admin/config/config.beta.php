<?php

$configProduction = require ROOT_PATH . '/module/Admin/config/config.production.php';

$config =  array (
 		'cmsHost' => 'beta.siteproject.com',
);

return array_merge($configProduction,$config);
