<?php

$configProduction = require ROOT_PATH . '/module/Admin/config/config.production.php';

$config =  array (
    'cmsHost' => 'dev.siteproject.com',
);

return array_merge($configProduction,$config);
