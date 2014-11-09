<?php

namespace CloudDeploy\Command;

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
		
	protected function configure() {
		$this
			->setName('node:monitor')
			->setDescription('Check whether the deployment is current and upgrade if necessary ')
			->addArgument('deployment', InputArgument::REQUIRED, 'Name of deployment to check');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->input	= $input;
		$this->output	= $output;
		
		$deployment	= $this->getDeployment($this->input->getArgument('deployment'));
		
		$release = $this->getDeployService()->getCurrentReleaseVersion($deployment);
		
		$this->output->writeLn('<info>Compiling release and deployment information...</info>');
		$this->output->writeLn('  <comment>Release:</comment> '. str_pad(ucfirst($release->getVersionType()), 6) .' : ' .  $release->getVersionName());
		$this->output->writeLn('  <comment>Current:</comment> '. $this->determineCurrentCheckout($deployment));	
		
		$upgrade_required = false;
		switch ( $release->getVersionType() ) {
			case Release::TYPE_BRANCH:
				$current_branch = $deployment->getCurrentBranch();
				if ( $current_branch ) {
					$this->fetchBranch($deployment, $current_branch);
				}
				if ( !$current_branch
					|| $current_branch->getName() != $release->getVersionName()
					|| $current_branch->getSha() != $deployment->getBranch($release->getVersionName())->getSha() )
					{
					$upgrade_required = true;
				}
				break;
				
			case Release::TYPE_TAG:
				$current_tag = $deployment->getCurrentTag();
				$upgrade_required = ( !$current_tag || $release->getVersionName() != $current_tag->getName() );
				break;
				
			case Release::TYPE_COMMIT:
				$current_commit = $deployment->getCurrentCommit();
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
	 * @param Deployment $deployment
	 * @return string
	 */
	private function determineCurrentCheckout($deployment) {
		if ( $current_branch = $deployment->getCurrentBranch() ) {
			return 'Branch : ' . $current_branch->getName();
		} else if ( $current_tag = $deployment->getCurrentTag() ) {
			return 'Tag    : ' . $current_tag->getName();
		} else {
			return 'Commit : '. $deployment->getCurrentCommit()->getSha(true);
		}
	}
	
	/**
	 * @param Deployment $deployment
	 */
	private function fetchBranch(Deployment $deployment, \GitElephant\Objects\Branch $branch) {
		$this->output->writeLn(sprintf('<info>Fetching latest changes on branch "%s"...</info>', $branch->getName()));
		$deployment->fetch('origin', $branch->getName());
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
