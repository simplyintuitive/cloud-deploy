<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/AppKernel.php';

$kernel	= new app\AppKernel('prod', false);

return $kernel->getApp();