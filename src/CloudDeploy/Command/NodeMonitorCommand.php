<?php

namespace CloudDeploy\Command;

use Exception;
use CloudDeploy\Git\Deployment;
use CloudDeploy\Model\Release;
use CloudDeploy\Service\DeploymentService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NodeMonitorCommand extends Command {
	
	/** @var InputInterface */
	private $input;
	
	/** @var OutputInterface */
	private $output;

	/** @var Deployment */
	private $deployment;
		
	protected function configure() {
		$this
			->setName('node:monitor')
			->setDescription('Check whether the deployment is current and upgrade if necessary ')
			->addArgument('deployment', InputArgument::REQUIRED, 'Name of deployment to check');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->input	= $input;
		$this->output	= $output;
		
		$this->deployment	= $this->getDeployment($this->input->getArgument('deployment'));
		
		$release = $this->getDeployService()->getCurrentReleaseVersion($this->deployment);
		
		$this->output->writeLn('<info>Compiling release and deployment information...</info>');
		$this->output->writeLn('  <comment>Release:</comment> '. str_pad(ucfirst($release->getVersionType()), 6) .' : ' .  $release->getVersionName());
		$this->output->writeLn('  <comment>Current:</comment> '. $this->determineCurrentCheckout($this->deployment));
		
		$upgrade_required = false;
		switch ( $release->getVersionType() ) {
			case Release::TYPE_BRANCH:
				$current_branch = $this->deployment->getCurrentBranch();
				$upgrade_required = (
					!$current_branch
					|| $current_branch->getName() != $release->getVersionName()
					|| !$this->fetch($this->deployment)
					|| !$this->deployment->isCurrentBranchUpToDate()
				);
				break;
				
			case Release::TYPE_TAG:
				$current_tag = $this->deployment->getCurrentTag();
				$upgrade_required = ( !$current_tag || $release->getVersionName() != $current_tag->getName() );
				break;
				
			case Release::TYPE_COMMIT:
				$current_commit = $this->deployment->getCurrentCommit();
				$upgrade_required = ( $release->getVersionName() != $current_commit );
				break;
		}
		
		if ( $upgrade_required ) {
			$this->output->writeLn('<info>Upgrading...</info>');
			$this->getDeployService()->do_upgrade($release, gethostname());
			$this->output->writeLn('<info>Done!</info>');
		} else {
			$this->output->writeLn('<info>Currently at the correct version</info>');
		}
	}
	
	/**
	 * @return string
	 */
	private function determineCurrentCheckout() {
		if ( $current_branch = $this->deployment->getCurrentBranch() ) {
			return 'Branch : ' . $current_branch->getName();
		} else if ( $current_tag = $this->deployment->getCurrentTag() ) {
			return 'Tag    : ' . $current_tag->getName();
		} else {
			return 'Commit : '. $this->deployment->getCurrentCommit()->getSha(true);
		}
	}
	
	/**
	 * @return boolean
	 */
	private function fetch() {
		try {
			$this->output->writeLn(sprintf('<info>Fetching latest changes on origin...</info>'));
			$this->deployment->fetch('origin');
			return true;
		} catch ( Exception $e ) {
			return false;
		}
		
	}
	
	/**
	 * @return DeploymentService
	 */
	private function getDeployService() {
		return $this->getSilexApplication()['cloud-deploy'];
	}
	
	/**
	 * @param string $name
	 * @return Deployment
	 */
	private function getDeployment($name) {
		return $this->getDeployService()->getDeployment($name);
	}
}
