<?php
require_once(__DIR__.'/../conf.php');
require_once(__DIR__.'/models.php');
require_once(__DIR__.'/db.php');
require_once(__DIR__.'/housekeeping.php');
require_once(__DIR__.'/lang.php');
if(file_exists(__DIR__.'/appstore.php')) require_once(__DIR__.'/appstore.php');
if(file_exists(__DIR__.'/playstore.php')) require_once(__DIR__.'/playstore.php');
require_once(__DIR__.'/account.php');
require_once(__DIR__.'/api-data.php');
require_once(__DIR__.'/api-account.php');

// init db connection
$db = new db();
