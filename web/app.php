<?php
ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$kernal	= new app\AppKernel('prod', false);