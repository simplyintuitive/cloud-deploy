<?php

namespace CloudDeploy\Model;

use DateTime;
use CloudDeploy\Git\Deployment;

class Release {
	
	/** @var int32 */
	private $id;
	
	/** @var Deployment */
	private $deployment;
	
	/** @var string */
	private $versionType;
	
	/** @var string */
	private $versionName;
		
	/** @var DateTime */
	private $date;
	
	/**
	 * @param Deployment $deployment
	 * @param array $row_data
	 */
	public function __construct(Deployment $deployment, array $row_data) {
		$this->deployment = $deployment;
		$this->id         = $row_data['release_id'];
		$this->date       = new DateTime($row_data['release_date']);
		list($this->versionType, $this->versionName) = explode(':', $row_data['version']);
	}

	/**
	 * @return int32
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return Deployment
	 */
	public function getDeployment() {
		return $this->deployment;
	}

	/**
	 * @return string
	 */
	public function getVersionType() {
		return $this->versionType;
	}
	
	/**
	 * @return string
	 */
	public function getVersionName() {
		return $this->versionName;
	}
	
	public function getVersionCommit() {
		$method	= 'get' . ucfirst($this->getVersionType());
		return $this->deployment->getCommit($this->deployment->{$method}($this->getVersionName())->getSha());
	}
	
	/**
	 * @return DateTime
	 */
	public function getDate() {
		return $this->date;
	}
}
