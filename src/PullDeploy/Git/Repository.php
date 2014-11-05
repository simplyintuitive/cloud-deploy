<?php

namespace PullDeploy\Git;

use Exception;

use GitElephant\Repository as GitRepository;

class Repository {

	private $repository;
	
	public function __construct($path_to_repo) {
		$this->repository = new GitRepository($path_to_repo);
	}
	
	public function getCurrentVersion() {
		return $this->repository->getCommit();
	}
	
	public function getTag($tag_name) {
		if ( !$tag = $this->repository->getTag($tag_name) ) {
			throw new Exception(sprintf('Tag %s does not exist', $tag_name));
		}
		
		return $tag;
	}
	
	public function getAllTags() {
		return $this->repository->getTags();
	}
	
	public function getBranch($branch_name) {
		if ( !$branch = $this->repository->getBranch($branch_name) ) {
			throw new Exception(sprintf('Branch %s does not exist', $branch_name));
		}
		
		return $branch;
	}
	
	public function getAllBranches() {
		return $this->repository->getBranches();
	}
	
	public function getCurrentBranch() {
		return $this->repository->getMainBranch();
	}

}