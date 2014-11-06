<?php

namespace CloudDeploy\Git;

use Exception;

use GitElephant\Objects\Branch;
use GitElephant\Objects\Tag;
use GitElephant\Repository as GitRepository;

class Deployment {

	/** @var string */
	private $name;
	
	/** @var GitRepository */
	private $repository;
	
	/**
	 * @param string $name
	 * @param array $config
	 */
	public function __construct($name, array $config) {
		$this->name       = $name;
		$this->repository = new GitRepository($config['path']);
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getCurrentVersion() {
		return $this->repository->getCommit();
	}

	/**
	 * 
	 * @param string $tag_name
	 * @return Tag
	 * @throws Exception
	 */
	public function getTag($tag_name) {
		if ( !$tag = $this->repository->getTag($tag_name) ) {
			throw new Exception(sprintf('Tag %s does not exist', $tag_name));
		}
		
		return $tag;
	}
	
	/**
	 * @return []Tag
	 */
	public function getAllTags() {
		return $this->repository->getTags();
	}
	
	/**
	 * @param string $branch_name
	 * @return Branch
	 * @throws Exception
	 */
	public function getBranch($branch_name) {
		if ( !$branch = $this->repository->getBranch($branch_name) ) {
			throw new Exception(sprintf('Branch %s does not exist', $branch_name));
		}
		
		return $branch;
	}
	
	/**
	 * 
	 * @return []Branch
	 */
	public function getAllBranches() {
		return $this->repository->getBranches();
	}
	
	/**
	 * @return Branch
	 */
	public function getCurrentBranch() {
		return $this->repository->getMainBranch();
	}

}