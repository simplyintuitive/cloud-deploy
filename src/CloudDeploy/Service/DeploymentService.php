<?php

namespace CloudDeploy\Service;

use CloudDeploy\Git\Deployment;
use CloudDeploy\Model\Release;
use Silex\Application;

class DeploymentService
{
	/** @var Application */
	private $app;
	
	/** @var []Deployments */
	private $deployments = [];
	
	/**
	 * @param Application $app
	 */
    public function __construct(Application $app) {
		$this->app = $app;
	}
	
	/**
	 * Return the current release version for the deployment
	 * 
	 * @param Deployment $deployment
	 * @return Release
	 */
	public function getCurrentReleaseVersion(Deployment $deployment) {
		$sql = "SELECT * FROM releases WHERE deployment = ? ORDER BY release_date DESC LIMIT 1";
		$row = $this->app['db']->fetchAssoc($sql, array($deployment->getName()));
		
		
		return new Release($deployment, $row);
	}
	
	/**
	 * @param string $name
	 * @return Deployment
	 */
	public function getDeployment($name) {
		if ( !isset($this->deployments[$name]) ) {
			$this->deployments[$name]	= new Deployment($name, $this->app['config']['deployments'][$name]);
		}
		
		return $this->deployments[$name];
	}
}