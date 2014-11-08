<?php

namespace CloudDeploy\Model;

use DateTime;
use CloudDeploy\Model\Release;

class Upgrade {
	
	const STATUS_STARTED    = 'started';
	const STATUS_FETCH      = 'fetching';
	const STATUS_CHECKOUT   = 'checking out';
	const STATUS_PULL       = 'pulling';
	const STATUS_SUBMODULE  = 'checking out submodules';
	const STATUS_COMPLETE   = 'complete';
	const STATUS_ABORT      = 'aborted';
	
	/** @var int32 */
	private $id;
	
	/** @var Release */
	private $release;
	
	/** @var string */
	private $node;
	
	/** @var string */
	private $status;
		
	/** @var DateTime */
	private $startDate;
	
	/** @var DateTime */
	private $finishDate;
	
	/**
	 * @param Release $release
	 * @param array $row_data
	 */
	public function __construct(Release $release = null, array $row_data = []) {
		$this->setRelease($release);
		
		if ( $row_data ) {
			if ( $row_data['upgrade_start_date'] ) {
				$row_data['upgrade_start_date'] = new DateTime($row_data['upgrade_start_date']);
			}
			if ( $row_data['upgrade_finish_date'] ) {
				$row_data['upgrade_finish_date'] = new DateTime($row_data['upgrade_finish_date']);
			}

			$this->setId($row_data['upgrade_id']);
			$this->setNode($row_data['node']);
			$this->setStartDate($row_data['upgrade_start_date']);
			$this->setFinishDate($row_data['upgrade_finish_date']);
			$this->setStatus($row_data['status']);
		}
	}

	/**
	 * @return int32
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param int32 $id
	 * @return $this
	 */
	public function setId($id) {
		$this->id = $id;
		
		return $this;
	}

	/**
	 * @return Release
	 */
	public function getRelease() {
		return $this->release;
	}
	
	/**
	 * @param Release $release
	 * @return $this
	 */
	public function setRelease(Release $release) {
		$this->release = $release;
		
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNode() {
		return $this->node;
	}
	
	/**
	 * @param string $node
	 * @return $this;
	 */
	public function setNode($node) {
		$this->node = $node;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * @param string $status
	 * @return $this
	 */
	public function setStatus($status) {
		$this->status = $status;
		
		return $this;
	}
	
	/**
	 * @return DateTime
	 */
	public function getStartDate() {
		return $this->startDate;
	}
	
	/**
	 * @return DateTime
	 */
	public function setStartDate(DateTime $date) {
		$this->startDate = $date;
		
		return $this;
	}
	
	/**
	 * @return DateTime
	 */
	public function getFinishDate() {
		return $this->finishDate;
	}
	
	/**
	 * @return DateTime
	 */
	public function setFinishDate(DateTime $date) {
		$this->finishDate = $date;
		
		return $this;
	}
}
