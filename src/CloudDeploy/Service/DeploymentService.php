<?php

namespace CloudDeploy\Service;

use DateTime;
use Exception;
use PDO;
use CloudDeploy\Git\Deployment;
use CloudDeploy\Model\Release;
use CloudDeploy\Model\Upgrade;
use Silex\Application;
use Doctrine\DBAL\Connection as DbConnection;

class DeploymentService {
	
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
	 * @param string $name
	 * @return Deployment
	 * @throws Exception
	 */
	public function getDeployment($name) {
		if ( !isset($this->deployments[$name]) ) {
			if ( !isset($this->app['config']['deployments'][$name]) ) {
				throw new Exception(sprintf('Deployment "%s" has not been defined in config.yml', $name));
			}
			$this->deployments[$name]	= new Deployment($name, $this->app['config']['deployments'][$name]);
		}

		return $this->deployments[$name];
	}

	/**
	 * @param Deployment $deployment
	 * @param int $limit
	 * @return Release[]
	 */
	public function getDeploymentReleases(Deployment $deployment, $limit = null) {
		$sql = "SELECT * FROM releases WHERE deployment = ? ORDER BY release_date DESC". ( $limit ? " LIMIT {$limit}" : '' );
		$result = $this->getDb()->executeQuery($sql, [$deployment->getName()]);

		$releases = [];
		while ( $row = $result->fetch(PDO::FETCH_ASSOC) ) {
			$releases[] = new Release($deployment, $row);
		}

		return $releases;
	}

	/**
	 * Return the current release version for the deployment
	 *
	 * @param Deployment $deployment
	 * @return Release
	 */
	public function getCurrentReleaseVersion(Deployment $deployment) {
		$releases	= $this->getDeploymentReleases($deployment, 1);
		if ( 0 == count($releases) ) {
			throw new \Exception('There are no releases.');
		}

		return $releases[0];
	}

	/**
	 * Get a specific release
	 * 
	 * @param Deployment $deployment
	 * @param int $release_id
	 * @return Release
	 * @throws Exception
	 */
	public function getRelease(Deployment $deployment, $release_id) {
		$sql = "SELECT * FROM releases WHERE release_id = ?";
		$row = $this->getDb()->fetchAssoc($sql, [$release_id]);

		if ( !$row ) {
			throw new \Exception(sprintf('Release \'%s\' does not exist.', $release_id));
		}

		return new Release($deployment, $row);
	}

	/**
	 * Get upgrades for supplied release
	 *
	 * @param Release $release
	 * @return Upgrade[]
	 */
	public function getReleaseUpgrades(Release $release) {
		$sql = "SELECT * FROM upgrades WHERE release_id = ? ORDER BY upgrade_start_date";
		$result = $this->getDb()->executeQuery($sql, [$release->getId()]);

		$upgrades = [];
		while ( $row = $result->fetch(PDO::FETCH_ASSOC) ) {
			$upgrades[] = new Upgrade($release, $row);
		}

		return $upgrades;
	}
	
	/**
	 * @param Release $release
	 * @param type $node
	 * @return Upgrade
	 * @throws Exception - rethrows any exceptions thrown
	 */
	public function do_upgrade(Release $release, $node) {
		$upgrade = $this->createUpgrade($release, $node);
		
		try {
			$this
				->fetchUpgrade($upgrade)
				->checkoutUpgrade($upgrade)
				->completeUpgrade($upgrade);
		} catch ( Exception $e ) {
			$this->abortUpgrade($upgrade);
			throw $e;
		}
		
		return $upgrade;
	}
	
	/**
	 * @param Upgrade $upgrade
	 * @return $this
	 */
	private function fetchUpgrade(Upgrade $upgrade) {
		$this->updateUpgradeStatus($upgrade, Upgrade::STATUS_FETCH);
		$upgrade->getRelease()->getDeployment()->fetch();
		
		return $this;
	}
	
	/**
	 * @param Upgrade $upgrade
	 * @return $this
	 */
	private function checkoutUpgrade(Upgrade $upgrade) {
		$this->updateUpgradeStatus($upgrade, Upgrade::STATUS_CHECKOUT);
		
		$upgrade->getRelease()->getDeployment()->checkout($upgrade->getRelease()->getVersionName());
		
		if ( Release::TYPE_BRANCH == $upgrade->getRelease()->getVersionType() ) {
			$this->updateUpgradeStatus($upgrade, Upgrade::STATUS_PULL);
			$upgrade->getRelease()->getDeployment()->pull('origin', $upgrade->getRelease()->getVersionName());
		}
		
		return $this;
	}
	
	/**
	 * @param Upgrade $upgrade
	 * @return $this
	 */
	private function completeUpgrade(Upgrade $upgrade) {
		$this->updateUpgradeStatus($upgrade, Upgrade::STATUS_COMPLETE);
		
		return $this;
	}
	
	/**
	 * @param Upgrade $upgrade
	 * @return $this
	 */
	private function abortUpgrade(Upgrade $upgrade) {
		$this->updateUpgradeStatus($upgrade, Upgrade::STATUS_ABORT);
		
		return $this;
	}
	
	/**
	 * @param Release $release
	 * @param string $node
	 * @return Upgrade
	 */
	private function createUpgrade(Release $release, $node) {
		$date = new DateTime();
		
		$upgrade = new Upgrade($release);
		$upgrade
			->setNode($node)
			->setStartDate($date)
			->setFinishDate($date)
			->setStatus(Upgrade::STATUS_STARTED);
		
		$this->getDb()->insert('upgrades', [
			'release_id' => (int) $upgrade->getRelease()->getId(),
			'node' => $upgrade->getNode(),
			'upgrade_start_date' => $upgrade->getStartDate()->format('Y-m-d H:i:s'),
			'upgrade_finish_date' => $upgrade->getFinishDate()->format('Y-m-d H:i:s'),
			'status' => $upgrade->getStatus(),
		]);
		
		$upgrade->setId($this->getDb()->lastInsertId());
		
		return $upgrade;
	}
	
	/**
	 * @param Upgrade $upgrade
	 * @param string $status
	 */
	private function updateUpgradeStatus(Upgrade $upgrade, $status) {
		$upgrade
			->setStatus($status)
			->setFinishDate(new DateTime());
		
		$this->getDb()->update('upgrades',
			[
				'status' => $upgrade->getStatus(),
				'upgrade_finish_date' => $upgrade->getFinishDate()->format('Y-m-d H:i:s'),
			],
			['upgrade_id' => (int) $upgrade->getId()]
		);
	}
	
	/**
	 * @return DbConnection
	 */
	private function getDb() {
		return $this->app['db'];
	}
}