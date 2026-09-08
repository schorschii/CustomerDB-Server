<?php

// static imports
require_once(__DIR__.'/conf.php');
require_once(__DIR__.'/lib/models.php');
require_once(__DIR__.'/lib/db.php');
require_once(__DIR__.'/lib/housekeeping.php');
require_once(__DIR__.'/lib/lang.php');
require_once(__DIR__.'/lib/account.php');
require_once(__DIR__.'/lib/api-data.php');
require_once(__DIR__.'/lib/api-account.php');

// dynamic class imports
spl_autoload_register(function ($class) {
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.class.php';
	$filePath = __DIR__.'/lib/'.$file;
	if(file_exists($filePath)) {
		require_once($filePath);
		return true;
	}
	return false;
});

// init db connection
$db = new db();
