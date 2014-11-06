<?php

namespace CloudDeploy\Command;

use CloudDeploy\Git\Deployment;
use CloudDeploy\Service\DeploymentService;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command {
	
	/** @var InputInterface */
	private $input;
	
	/** @var OutputInterface */
	private $output;
		
	protected function configure() {
		$this
			->setName('check-release')
			->setDescription('Check whether the current release is current')
			->addArgument('deployment', InputArgument::REQUIRED, 'Name of deployment to check');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->input	= $input;
		$this->output	= $output;
		
		$deployment	= $this->getDeployment($this->input->getArgument('deployment'));
		$release = $this->getDeployService()->getCurrentReleaseVersion($deployment);
			
		$this->output->writeLn('<comment>Release:</comment>        '. $release->getVersionType() .' : ' .  $release->getVersionName());
		$this->output->writeLn('<comment>Release Commit:</comment> '. $release->getVersionCommit());
		$this->output->writeLn('<comment>Current Commit:</comment> '. $deployment->getCurrentCommit());		
		
		if ( $deployment->getCurrentCommit() != $release->getVersionCommit() ) {
			$this->output->writeLn('<info>Deploying...</info>');
			$this->getDeployService()->deploy($release);
			$this->output->writeLn('Done!');
		} else {
			$this->output->writeLn('<info>Currently at the correct version</info');
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
