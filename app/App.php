<?php

namespace app;

use CloudDeploy\Controller\DeploymentControllerProvider;
use CloudDeploy\Service\DeploymentService;
use DerAlex\Silex\YamlConfigServiceProvider;
use Knp\Provider\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class App extends Application {

	/**
	 * @param string $env - the environment
	 */
	public function __construct($env = 'prod', $debug = false) {
		parent::__construct(['env' => $env, 'debug' => $debug]);

		$this
			->registerErrorHandling()
			->registerServices()
			->registerRoutes();
	}

	/**
	 * @return $this
	 */
	private function registerErrorHandling() {
		$app = $this;
		$this->error(function (\Exception $e, $code) use ($app) {
			if ($app['debug']) {
				die(sprintf('Exception %s thrown: %s', get_class($e), $e->getMessage()));
			} else {
				$this->abort(500, 'An error has occurred');
			}
		});

		return $this;
	}

	/**
	 * @return $this
	 */
	private function registerServices() {
		$this->register(new YamlConfigServiceProvider(__DIR__ . '/config/config_'. $this['env'] .'.yml'));
		$this->register(new UrlGeneratorServiceProvider());
		$this->register(new DoctrineServiceProvider(), ['db.options' => $this['config']['database']]);
		//$this->register(new FormServiceProvider());
		//$this->register(new ValidatorServiceProvider());
		$this->register(new ConsoleServiceProvider(), array(
			'console.name' => 'ConsoleApp',
			'console.version' => '1.0.0',
			'console.project_directory' => __DIR__ . '/..'
		));

		$this['cloud-deploy'] = new DeploymentService($this);

		$twig_options = [];
		if ( 'prod' == $this['env'] ) {
			$twig_options['cache'] = __DIR__ . '/cache/' . $this['env'] .'/twig/';
		}
		$this->register(new TwigServiceProvider(), [
			'twig.path' => __DIR__ . '/../src/CloudDeploy/Resources/views',
			'twig.options' => $twig_options,
		]);

		return $this;
	}

	/**
	 *
	 * @return $this
	 */
	private function registerRoutes() {
		$this->mount('/{deployment}/', new DeploymentControllerProvider());

		return $this;
	}
}
