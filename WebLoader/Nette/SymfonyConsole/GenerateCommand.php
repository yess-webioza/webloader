<?php

namespace WebLoader\Nette\SymfonyConsole;

use Nette;
use Symfony;
use WebLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate Command
 */
class GenerateCommand extends Symfony\Component\Console\Command\Command
{

	/** @var WebLoader\Compiler[] */
	private $compilers = [];

	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct();

		$compilers = $container->findByType(WebLoader\Compiler::class);
		foreach ($compilers as $compilerName) {
			$this->compilers[$compilerName] = $container->getService($compilerName);
		}
	}

	protected function configure()
	{
		$this->setName('webloader:generate')
			->setDescription('Generates files.')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Generate if not modified.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$force = $input->getOption('force');

		$nofiles = true;
	        foreach ($this->compilers as $compiler) {
			$files = $compiler->generate(!$force);
			foreach ($files as $file) {
				$output->writeln($file->file);
				$nofiles = false;
			}
		}

		if ($nofiles) {
			$output->writeln('No files generated.');
		}
	}

}

