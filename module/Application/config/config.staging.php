<?php
$configProduction = require ROOT_PATH . '/module/Application/config/config.production.php';

$config = array(
// 	'writableDir' => array(
// 		'base'            => '',
// 		'dataCache'       => ROOT_PATH . '/data/data-cache/',
// 		'log'             => '/var/log/www/siteproject/',
// 		'styles'          => ROOT_PATH . '/public/styles/',
// 	),
	'imageServer'            => 'http://beta.jykc.windfindtech.net/images/',
);
return array_merge($configProduction,$config);

