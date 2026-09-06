<?php

// static imports
require_once(__DIR__.'/../conf.php');
require_once(__DIR__.'/models.php');
require_once(__DIR__.'/db.php');
require_once(__DIR__.'/housekeeping.php');
require_once(__DIR__.'/lang.php');
require_once(__DIR__.'/account.php');
require_once(__DIR__.'/api-data.php');
require_once(__DIR__.'/api-account.php');

// dynamic class imports
spl_autoload_register(function ($class) {
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.class.php';
	$filePath = __DIR__.'/'.$file;
	if(file_exists($filePath)) {
		require_once($filePath);
		return true;
	}
	return false;
});

// init db connection
$db = new db();
