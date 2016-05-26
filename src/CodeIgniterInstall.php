<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\ClientInterface;
use ZipArchive;

class CodeIgniterInstall extends Command{

	private $client;

	public function __construct(ClientInterface $client){

		$this->client = $client;
		parent::__construct();
	}

	public function configure(){

		$this->setName('install')
			 ->setDescription('Download and install the framework')
			 ->addArgument('directory', InputArgument::REQUIRED);
	}

	public function execute(InputInterface $input, OutputInterface $output){

		$directory = getcwd() . "/" . $input->getArgument('directory');

		$output->writeln("<comment> Downloading and Extracting...! </comment>");

		$this->assertApplicationDoesNotExist($directory, $output);

		$this->download($zipFile = $this->makeFileName())
			 ->extract($zipFile, $directory)
			 ->cleanUp($zipFile);

		$output->writeln("<info> CodeIgniter Installed! </info>");

	}

	private function assertApplicationDoesNotExist($directory, OutputInterface $output){

		if(is_dir($directory)){
			$output->writeln('<error> Directory already exist! </error>');
			exit(1);
		}
	}

	private function makeFileName(){
		return getcwd() . 'codeigniter_' . md5(time().uniqid()) . '.zip';
	}

	private function download($zipFile){
		$response = $this->client->get('https://github.com/bcit-ci/CodeIgniter/archive/3.0.6.zip')->getBody();

		file_put_contents($zipFile, $response);

		return $this;
	}

	private function extract($zipFile, $directory){
		$archive = new ZipArchive;
		$archive->open($zipFile);
		$archive->extractTo($directory);
		$archive->close();

		$files = array_diff(scandir($directory . '/CodeIgniter-3.0.6'), array('.', '..'));

		foreach ($files as $file) {
				rename($directory ."/" ."CodeIgniter-3.0.6"."/". $file, $directory ."/". $file);
		}

		rmdir($directory . '/CodeIgniter-3.0.6');

		return $this;
	}

	private function cleanUp($zipFile){
		@chmod($zipFile, 0777);
		@unlink($zipFile);
		return $this;
	}

}