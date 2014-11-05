<?php
ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php'; 

use DerAlex\Silex\YamlConfigServiceProvider;
use PullDeploy\Git\Repository;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

$app = new Application();
$app->register(new YamlConfigServiceProvider(__DIR__ . '/../app/config.yml'));
$app->register(new UrlGeneratorServiceProvider());
//$app->register(new ValidatorServiceProvider()); 
//$app->register(new DoctrineServiceProvider(), array(
//    'db.options' => array(
//        'driver'   => 'pdo_sqlite',
//        'path'     => __DIR__.'/app.db',
//    ),
//));
//$app->register(new FormServiceProvider());

$app->error(function (\Exception $e, $code) {
	die(sprintf('Exception %s thrown: %s', get_class($e), $e->getMessage()));
});

$app['repo'] = new Repository($app['config']['repository']['path']);

$app->get('/{object_type}/', function($object_type) use($app) {
	$method	= 'getAll' . ucfirst($object_type);
	$output	= "All {$object_type}:";
	foreach ( $app['repo']->{$method}() as $object ) {
		$link = $app['url_generator']->generate('view_object', array('object_type' => $object_type, 'name' => $app->escape($object->getName())));
		$output	.= "\n<a href=\"{$link}\">{$app->escape($object->getName())}</a> = {$app->escape($object->__toString())}";
	}
	return "<pre>{$output}</pre>"; 
})
->bind('all_objects');

$app->get('/{object_type}/current/', function($object_type) use($app) {
	$method	= 'getCurrent' . ucfirst($object_type);
	return "Current {$object_type}: ".$app->escape($app['repo']->{$method}()); 
})
->bind('current_object');

$app->get('/{object_type}/{name}/', function($object_type, $name) use($app) {
	$method	= 'get' . ucfirst($object_type);
	if ( substr($method, -2, 2) == 'es' ) $method = substr($method, 0, strlen($method) - 2);
	if ( substr($method, -1, 1) == 's' ) $method = substr($method, 0, strlen($method) - 1);	
	return $app->escape($app['repo']->{$method}($name)->__toString());
})
->bind('view_object');

$app->run();