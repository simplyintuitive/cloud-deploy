<?php
require_once(__DIR__ . '/../bootstrap.php');

$app = new app\App('prod', false);
$app->run();