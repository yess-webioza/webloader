<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\Schema\Expect;
use Nette\Schema\Helpers as SchemaHelpers;
use Nette\Schema\Schema;
use Nette\Utils\Finder;
use SplFileInfo;
use WebLoader\Compiler as WebloaderCompiler;
use WebLoader\DefaultOutputNamingConvention;
use WebLoader\FileCollection;
use WebLoader\FileNotFoundException;
use WebLoader\Nette\Diagnostics\Panel;
use WebLoader\Nette\SymfonyConsole\GenerateCommand;

/**
 * @author Jan Marek
 */
class Extension extends CompilerExtension
{
	public const DEFAULT_TEMP_PATH = 'webtemp';
	public const EXTENSION_NAME = 'webloader';

	private string $appDir;
	private string $wwwDir;
	private bool $debugMode;


	public function __construct(string $appDir, string $wwwDir, bool $debugMode)
	{
		$this->appDir = $appDir;
		$this->wwwDir = $wwwDir;
		$this->debugMode = $debugMode;
	}


	private function getJsConfigSchema(bool $useDefaults = false): Schema
	{
		$checkLastModified = $useDefaults ? true : null;
		$debug = $useDefaults ? false : null;
		$sourceDir = $useDefaults ? ($this->wwwDir . '/js') : null;
		$tempDir = $useDefaults ? ($this->wwwDir . '/' . self::DEFAULT_TEMP_PATH) : null;
		$tempPath = $useDefaults ? self::DEFAULT_TEMP_PATH : null;
		$async = $useDefaults ? false : null;
		$defer = $useDefaults ? false : null;
		$absoluteUrl = $useDefaults ? false : null;
		$namingConvention = $useDefaults ? ('@' . $this->prefix('jsNamingConvention')) : null;

		return Expect::structure([
			'checkLastModified' => Expect::bool($checkLastModified),
			'debug' => Expect::bool($debug),
			'sourceDir' => Expect::string($sourceDir),
			'tempDir' => Expect::string($tempDir),
			'tempPath' => Expect::string($tempPath),
			'files' => Expect::array(),
			'watchFiles' => Expect::array(),
			'remoteFiles' => Expect::array(),
			'filters' => Expect::array(),
			'fileFilters' => Expect::array(),
			'async' => Expect::bool($async),
			'defer' => Expect::bool($defer),
			'nonce' => Expect::string()->nullable(),
			'absoluteUrl' => Expect::bool($absoluteUrl),
			'namingConvention' => Expect::string($namingConvention),
		]);
	}


	private function getCssConfigSchema(bool $useDefaults = false): Schema
	{
		$checkLastModified = $useDefaults ? true : null;
		$debug = $useDefaults ? false : null;
		$sourceDir = $useDefaults ? ($this->wwwDir . '/css') : null;
		$tempDir = $useDefaults ? ($this->wwwDir . '/' . self::DEFAULT_TEMP_PATH) : null;
		$tempPath = $useDefaults ? self::DEFAULT_TEMP_PATH : null;
		$async = $useDefaults ? false : null;
		$defer = $useDefaults ? false : null;
		$absoluteUrl = $useDefaults ? false : null;
		$namingConvention = $useDefaults ? ('@' . $this->prefix('cssNamingConvention')) : null;

		return Expect::structure([
			'checkLastModified' => Expect::bool($checkLastModified),
			'debug' => Expect::bool($debug),
			'sourceDir' => Expect::string($sourceDir),
			'tempDir' => Expect::string($tempDir),
			'tempPath' => Expect::string($tempPath),
			'files' => Expect::array(),
			'watchFiles' => Expect::array(),
			'remoteFiles' => Expect::array(),
			'filters' => Expect::array(),
			'fileFilters' => Expect::array(),
			'async' => Expect::bool($async),
			'defer' => Expect::bool($defer),
			'nonce' => Expect::string()->nullable(),
			'absoluteUrl' => Expect::bool($absoluteUrl),
			'namingConvention' => Expect::string($namingConvention),
		]);
	}


	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'jsDefaults' => $this->getJsConfigSchema(true),
			'cssDefaults' => $this->getCssConfigSchema(true),
			'js' => Expect::arrayOf($this->getJsConfigSchema()),
			'css' => Expect::arrayOf($this->getCssConfigSchema()),
			'debugger' => Expect::bool($this->debugMode),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = json_decode((string) json_encode($this->getConfig()), true);

		$builder->addDefinition($this->prefix('cssNamingConvention'))
			->setFactory('WebLoader\DefaultOutputNamingConvention::createCssConvention');

		$builder->addDefinition($this->prefix('jsNamingConvention'))
			->setFactory('WebLoader\DefaultOutputNamingConvention::createJsConvention');

		if ($config['debugger']) {
			$builder->addDefinition($this->prefix('tracyPanel'))
				->setType(Panel::class)
				->setArguments([$this->appDir]);
		}

		$builder->parameters['webloader'] = $config;

		$loaderFactoryTempPaths = [];

		foreach (['css', 'js'] as $type) {
			foreach ($config[$type] as $name => $wlConfig) {
				/** @var array $wlConfig */
				$wlConfig = array_filter($wlConfig);
				$wlConfig = SchemaHelpers::merge($wlConfig, $config[$type . 'Defaults']);

				if (!is_array($wlConfig)) {
					throw new CompilationException('Batch config not valid.');
				}

				$this->addWebLoader($builder, $type . ucfirst($name), $wlConfig);
				$loaderFactoryTempPaths[strtolower($name)] = $wlConfig['tempPath'];

				if (!is_dir($wlConfig['tempDir']) || !is_writable($wlConfig['tempDir'])) {
					throw new CompilationException(sprintf("You must create a writable directory '%s'", $wlConfig['tempDir']));
				}
			}
		}

		$builder->addDefinition($this->prefix('factory'))
			->setType(LoaderFactory::class)
			->setArguments([$loaderFactoryTempPaths, $this->name]);

		if (class_exists('Symfony\Component\Console\Command\Command')) {
			$builder->addDefinition($this->prefix('generateCommand'))
				->setType(GenerateCommand::class)
				->addTag('kdyby.console.command');
		}
	}


	private function addWebLoader(ContainerBuilder $builder, string $name, array $config): void
	{
		$filesServiceName = $this->prefix($name . 'Files');

		$files = $builder->addDefinition($filesServiceName)
			->setType(FileCollection::class)
			->setArguments([$config['sourceDir']]);

		foreach ($this->findFiles($config['files'], $config['sourceDir']) as $file) {
			$files->addSetup('addFile', [$file]);
		}

		foreach ($this->findFiles($config['watchFiles'], $config['sourceDir']) as $file) {
			$files->addSetup('addWatchFile', [$file]);
		}

		$files->addSetup('addRemoteFiles', [$config['remoteFiles']]);

		$compiler = $builder->addDefinition($this->prefix($name . 'Compiler'))
			->setType(WebloaderCompiler::class)
			->setArguments([
				'@' . $filesServiceName,
				$config['namingConvention'],
				$config['tempDir'],
			]);

		$compiler
			->addSetup('setAsync', [$config['async']])
			->addSetup('setDefer', [$config['defer']])
			->addSetup('setNonce', [$config['nonce']])
			->addSetup('setAbsoluteUrl', [$config['absoluteUrl']]);

		if ($builder->parameters['webloader']['debugger']) {
			$compiler->addSetup('@' . $this->prefix('tracyPanel') . '::addLoader', [
				$name,
				'@' . $this->prefix($name . 'Compiler'),
			]);
		}

		foreach ($config['filters'] as $filter) {
			$compiler->addSetup('addFilter', [$filter]);
		}

		foreach ($config['fileFilters'] as $filter) {
			$compiler->addSetup('addFileFilter', [$filter]);
		}

		if (isset($config['debug']) && $config['debug']) {
			$compiler->addSetup('enableDebugging');
		}

		$compiler->addSetup('setCheckLastModified', [$config['checkLastModified']]);

		// todo css media
	}


	// I have no clue what this is supposed to do...
	// public function afterCompile(ClassType $class): void
	// {
	// 	$types = $class->getProperty('types');
	// 	if (array_key_exists('webloader\\nette\\loaderfactory', $types)) {
	// 		$types['webloader\\loaderfactory'] = $types['webloader\\nette\\loaderfactory'];
	// 	}
	// 	if (array_key_exists('WebLoader\\Nette\\LoaderFactory', $types)) {
	// 		$types['WebLoader\\LoaderFactory'] = $types['WebLoader\\Nette\\LoaderFactory'];
	// 	}
	//
	// 	$init = $class->methods['initialize'];
	// 	$init->addBody('if (!class_exists(?, ?)) class_alias(?, ?);', ['WebLoader\\LoaderFactory', false, 'WebLoader\\Nette\\LoaderFactory', 'WebLoader\\LoaderFactory']);
	// }


	public function install(Configurator $configurator): void
	{
		$self = $this;
		$configurator->onCompile[] = function ($configurator, Compiler $compiler) use ($self): void {
			$compiler->addExtension($self::EXTENSION_NAME, $self);
		};
	}


	private function findFiles(array $filesConfig, string $sourceDir): array
	{
		$normalizedFiles = [];

		/** @var array|string $file */
		foreach ($filesConfig as $file) {
			// finder support
			if (is_array($file) && isset($file['files']) && (isset($file['in']) || isset($file['from']))) {
				$finder = Finder::findFiles($file['files']);

				if (isset($file['exclude'])) {
					$finder->exclude($file['exclude']);
				}

				if (isset($file['in'])) {
					$finder->in(is_dir($file['in']) ? $file['in'] : $sourceDir . DIRECTORY_SEPARATOR . $file['in']);
				} else {
					$finder->from(is_dir($file['from']) ? $file['from'] : $sourceDir . DIRECTORY_SEPARATOR . $file['from']);
				}

				$foundFilesList = [];
				foreach ($finder as $foundFile) {
					/** @var SplFileInfo $foundFile */
					$foundFilesList[] = $foundFile->getPathname();
				}

				natsort($foundFilesList);

				/** @var string $foundFilePathname */
				foreach ($foundFilesList as $foundFilePathname) {
					$normalizedFiles[] = $foundFilePathname;
				}

			} else {
				if (is_string($file)) {
					$this->checkFileExists($file, $sourceDir);
					$normalizedFiles[] = $file;
				}
			}
		}

		return $normalizedFiles;
	}


	protected function checkFileExists(string $file, string $sourceDir): void
	{
		if (!$this->fileExists($file)) {
			$tmp = rtrim($sourceDir, '/\\') . DIRECTORY_SEPARATOR . $file;
			if (!$this->fileExists($tmp)) {
				throw new FileNotFoundException(sprintf("Neither '%s' or '%s' was found", $file, $tmp));
			}
		}
	}


	/**
	 * Some servers seem to have problems under cron user with open_basedir restriction when using relative paths
	 */
	protected function fileExists(string $file): bool
	{
		$file = realpath($file);

		if ($file === false) {
			$file = '';
		}

		return file_exists($file);
	}
}
