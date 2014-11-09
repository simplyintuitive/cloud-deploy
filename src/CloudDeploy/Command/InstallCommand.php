<?php

namespace CloudDeploy\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
	/** @var OutputInterface */
	private $output;
	
	protected function configure() {
		$this
			->setName('cloud-deploy:install')
			->setDescription('Install the cloud deploy database');
	}
	
	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		
		$this->output->writeLn('<info>Installing...</info>');
		
		$config		= $this->getSilexApplication()['config'];
		$mysql_cmd	= $this->getMySqlCmd($config);
		$dbname		= $config['database']['dbname'];
		
		$this->createDb($mysql_cmd, $dbname);
		$this->createTables($mysql_cmd, $dbname);
		
		$this->output->writeLn('<info>Done!</info>');
	}
	
	/**
	 * Create the database if it does not already exist
	 * 
	 * @param string $mysql_cmd
	 * @param string $dbname
	 * @throws \RuntimeException
	 */
	private function createDb($mysql_cmd, $dbname) {
		// Attempt to query database - an exception will be thrown if it does not exist
		try {
			$this->getSilexApplication()['db']->fetchAssoc('SELECT 1');
		} catch (\PDOException $e) {
			if ( false === strpos($e->getMessage(), 'Access denied') ) {
				// Create database
				$sql = escapeshellarg(sprintf('CREATE DATABASE IF NOT EXISTS `%s`;', $dbname));
				$cmd = sprintf('echo %s | %s', $sql, $mysql_cmd);

				$process = new Process($cmd);
				$process->run(function ($type, $buffer) {});
				if (!$process->isSuccessful()) {
					throw new \RuntimeException(sprintf("An error occurred when attempting to create the database. Check user permissions.\n\n%s", $cmd));
				}
			} else {
				throw new \RuntimeException(sprintf("Unable to query the MySQL database due to access restrictions. Please check user has sufficient rights to SELECT, INSERT, UPDATE and DELETE.\n\n%s", $e->getMessage()));
			}
		}
	}
	
	/**
	 * Create the required tables if they do not exist
	 * 
	 * @param string $mysql_cmd
	 * @param string $dbname
	 * @throws \RuntimeException
	 */
	private function createTables($mysql_cmd, $dbname) {
		$cmd = sprintf('%s %s < %s', $mysql_cmd, $dbname, __DIR__ .'/../Resources/sql/install.sql');

        $process = new Process($cmd);
		$process->run(function ($type, $buffer) {});
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf("An error occurred when executing the mysql command.\n\n%s", $cmd));
        }
	}
	
	private function getMySqlCmd(array $config) {
		$host = escapeshellarg($config['database']['host']);
		$port = escapeshellarg((int) $config['database']['port']);
		$user = escapeshellarg($config['database']['user']);
		$pass = escapeshellarg($config['database']['password']);
		
		return sprintf('%s -u%s -p%s -h%s -P%s', $this->getMySql(), $user, $pass, $host, $port);
	}
	
	/**
	 * Return the path to the MySQL client executable
	 * 
	 * @return string
	 * @throws \RuntimeException
	 */
	private function getMySql() {
		$process = new Process('which mysql');
        $process->run();
		
		$mysql = trim($process->getOutput());
		
		if ( !$mysql ) {
			throw new \RuntimeException("The MySQL client executable could not be found using `which mysql`.");
		}
		
		return $mysql;
	}
}
