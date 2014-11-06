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
			->setName('do-upgrade')
			->setDescription('Check whether the deployment is current and upgrade if necessary ')
			->addArgument('deployment', InputArgument::REQUIRED, 'Name of deployment to check');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->input	= $input;
		$this->output	= $output;
		
		$deployment	= $this->getDeployment($this->input->getArgument('deployment'));
		$release = $this->getDeployService()->getCurrentReleaseVersion($deployment);
		
		$this->output->writeLn('<info>Compiling release and deployment information...</info>');
		$this->output->writeLn('<comment>Release:</comment>        '. $release->getVersionType() .' : ' .  $release->getVersionName());
		
		$release_commit = $release->getVersionCommit();
		$current_commit = $deployment->getCurrentCommit();
		
		$this->output->writeLn('<comment>Release Commit:</comment> '. $release_commit .' ('. $release_commit->getDatetimeAuthor()->format('Y-m-d H:i:s') .')');
		$this->output->writeLn('<comment>Current Commit:</comment> '. $current_commit .' ('. $current_commit->getDatetimeAuthor()->format('Y-m-d H:i:s') .')');		
		
		if ( $current_commit != $release_commit ) {
			$this->output->writeLn('<info>Deploying...</info>');
			$this->getDeployService()->do_upgrade($release, gethostname());
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
