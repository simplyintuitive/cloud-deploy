<?php

namespace CloudDeploy\Git;

use Exception;
use InvalidArgumentException;
use GitElephant\Objects\Branch;
use GitElephant\Objects\Commit;
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
	 * @return Commit
	 */
	public function getCurrentCommit() {
		return $this->repository->getCommit();
	}
	
	/**
	 * 
	 * @param string $commit
	 * @return Commit
	 * @throws Exception
	 */
	public function getCommit($commit) {
		if ( !$commit = $this->repository->getCommit($commit) ) {
			throw new Exception(sprintf('Commit %s does not exist', $commit));
		}
		
		return $commit;
	}
	
	/**
	 * @return Tag|null
	 */
	public function getCurrentTag() {
		foreach ( $this->repository->getTags() as $tag) {
			if ( $this->getCurrentCommit()->getSha() == $tag->getSha() ) {
				return $tag;
			}
		}
		
		return null;
	}

	/**
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
	 * @return Branch|null
	 */
	public function getCurrentBranch() {
		try {
			return $this->repository->getMainBranch();
		} catch ( InvalidArgumentException $e ) {
			return null;
		}
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
	 * @return []Branch
	 */
	public function getAllBranches() {
		return $this->repository->getBranches();
	}
	
	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasBranch($name) {
		return in_array($name, $this->repository->getBranches(true));
	}

	/**
	 * Determine if current branch is up to date with remote
	 *
	 * @return boolean
	 */
	public function isCurrentBranchUpToDate() {
		return ( $current_branch = $this->getCurrentBranch() && 0 == $this->repository->getLog('--' , /*$path=*/'HEAD..origin/' . $this->getCurrentBranch()->getName(), /*$limit=*/1)->count() );
	}
	
	/**
	 * Fetch objects from another repository.
	 * 
	 * @param string $from
	 * @param string $branch - commit reference
	 */
	public function fetch($from = null, $branch = null) {
		$this->repository->fetch($from, $branch);
	}

	/**
	 * Checkout a commit
	 * 
	 * @param string $ref - reference for commit
	 */
	public function checkout($ref) {
		$this->repository->checkout($ref);
	}
	
	/**
	 * @param string $from - remote repository
	 * @param string $branch - name of branch
	 */
	public function pull($from = null, $branch = null) {
		$this->repository->pull($from, $branch);
	}
}