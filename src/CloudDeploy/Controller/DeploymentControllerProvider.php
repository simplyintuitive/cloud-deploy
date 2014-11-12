<?php
namespace CloudDeploy\Controller;

use CloudDeploy\Git\Deployment;
use CloudDeploy\Service\DeploymentService;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

class DeploymentControllerProvider implements ControllerProviderInterface
{
	/** Application */
	private $app;
	
	/**
	 * @param Application $app
	 * @return ControllerCollection
	 */
    public function connect(Application $app)
    {
		$this->app	= $app;

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

		$controllers->get('/', function() use ($app) {
			return $app['twig']->render('index.html.twig', array(
				'deployments' => $app['config']['deployments'],
			));
		})
		->bind('all_deployments');

        $controllers->get('/{deployment}/', function($deployment) use ($app) {
			$deployment	= $this->getDeployment($deployment);

			try {
				$release = $this->getDeployService()->getCurrentReleaseVersion($deployment);
			} catch ( \Exception $e ) {
				$release = null;
			}

			return $app['twig']->render('Deployment/index.html.twig', [
				'deployment' => $deployment,
				'releases' => $this->getDeployService()->getDeploymentReleases($deployment),
			]);
		})
		->bind('deployment_index');

		$controllers->get('/{deployment}/release/{release_id}/', function($deployment, $release_id) use ($app) {
			$deployment	= $this->getDeployment($deployment);

			try {
				$release = $this->getDeployService()->getRelease($deployment, $release_id);
				$upgrades = $this->getDeployService()->getReleaseUpgrades($release);
			} catch ( \Exception $e ) {
				$release = null;
				$upgrades = [];
			}

			return $app['twig']->render('Release/index.html.twig', [
				'deployment' => $deployment,
				'release' => $release,
				'upgrades' => $upgrades,
			]);
		})
		->bind('release_index');

		$controllers->get('/{deployment}/{object_type}/', function($deployment, $object_type) use($app) {
			$method	= 'getAll' . ucfirst($object_type);
			$output	= "All {$object_type}:";
			foreach ( $this->getDeployment($deployment)->{$method}() as $object ) {
				$link = $app['url_generator']->generate('view_object', array('deployment' => $deployment, 'object_type' => $object_type, 'name' => $app->escape($object->getName())));
				$output	.= "\n<a href=\"{$link}\">{$app->escape($object->getName())}</a> = {$app->escape($object->__toString())}";
			}
			return "<pre>{$output}</pre>";
		})
		->bind('all_objects');

		$controllers->get('/{deployment}/{object_type}/current/', function($deployment, $object_type) use ($app) {
			$method	= 'getCurrent' . ucfirst($object_type);
			return "Current {$object_type}: " . $app->escape($this->getDeployment($deployment)->{$method}()); 
		})
		->bind('current_object');

		$controllers->get('/{deployment}/{object_type}/{name}/', function($deployment,$object_type, $name) use ($app) {
			$method	= 'get' . ucfirst($object_type);
			if ( substr($method, -2, 2) == 'es' ) $method = substr($method, 0, strlen($method) - 2);
			if ( substr($method, -1, 1) == 's' ) $method = substr($method, 0, strlen($method) - 1);	
			return $app->escape($this->getDeployment($deployment)->{$method}($name)->__toString());
		})
		->bind('view_object');

        return $controllers;
    }
	
	/**
	 * @return DeploymentService
	 */
	private function getDeployService() {
		return $this->app['cloud-deploy'];
	}
	
	/**
	 * @param string $name
	 * @return Deployment
	 */
	private function getDeployment($name) {
		return $this->getDeployService()->getDeployment($name);
	}
}