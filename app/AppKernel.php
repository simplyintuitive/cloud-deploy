<?php

namespace app;

use CloudDeploy\Controller\DeploymentControllerProvider;
use CloudDeploy\Service\DeploymentService;
use DerAlex\Silex\YamlConfigServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class AppKernel {
	
	/** @var Application */
	private $app;
	
	/**
	 * @param string $env - the environment
	 */
	public function __construct($env = 'prod', $debug = false) {
		$this->app = new Application();
		$this->app['env']	= $env;
		$this->app['debug']	= $debug;
		
		$this->app->error(function (\Exception $e, $code) {
			die(sprintf('Exception %s thrown: %s', get_class($e), $e->getMessage()));
		});
		
		$this
			->registerServices()
			->addRoutes();
		
		$this->app->run();
	}
	
	private function addRoutes() {
		$app	= $this->app;
		
		$app->mount('/{deployment}', new DeploymentControllerProvider());
		
		return $this;
	}
	
	private function registerServices() {
		$this->app->register(new YamlConfigServiceProvider(__DIR__ . '/config/config_'. $this->app['env'] .'.yml'));
		$this->app->register(new UrlGeneratorServiceProvider());		
		$this->app->register(new DoctrineServiceProvider(), ['db.options' => $this->app['config']['database']]);
		//$this->app->register(new FormServiceProvider());
		//$this->app->register(new ValidatorServiceProvider()); 
		
		$this->app['cloud-deploy'] = new DeploymentService($this->app);
		
		$this->app->register(new TwigServiceProvider(), array(
			'twig.path' => __DIR__.'/../src/CloudDeploy/Resources/views',
		));
		
		return $this;
	}
}
