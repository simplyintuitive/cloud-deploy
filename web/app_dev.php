<?php
require_once(__DIR__ . '/../bootstrap.php');

ini_set('display_errors', 1);

$app = new app\App('dev', true);
$app->run();