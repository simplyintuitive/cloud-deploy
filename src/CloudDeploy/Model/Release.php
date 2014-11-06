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
	private $version;
		
	/** @var DateTime */
	private $date;
	
	/**
	 * @param Deployment $deployment
	 * @param array $row_data
	 */
	public function __construct(Deployment $deployment, array $row_data) {
		$this->deployment = $deployment;
		$this->id         = $row_data['release_id'];
		$this->version    = $row_data['version'];
		$this->date       = new DateTime($row_data['release_date']);
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
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 * @return DateTime
	 */
	public function getDate() {
		return $this->date;
	}
}
