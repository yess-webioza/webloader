<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\DI\Container;
use Nette\Http\IRequest;
use WebLoader\Compiler;
use WebLoader\DefaultOutputNamingConvention;
use WebLoader\Contract\IOutputNamingConvention;

class LoaderFactory
{

	private IRequest $httpRequest;
	private Container $diContainer;
	private string $extensionName;

	/** @var array<string> */
	private array $tempPaths;


	/**
	 * @param array<string> $tempPaths
	 * @param string $extensionName
	 * @param IRequest $httpRequest
	 * @param Container $diContainer
	 */
	public function __construct(
		array $tempPaths,
		string $extensionName,
		IRequest $httpRequest,
		Container $diContainer
	) {
		$this->httpRequest = $httpRequest;
		$this->diContainer = $diContainer;
		$this->tempPaths = $tempPaths;
		$this->extensionName = $extensionName;
	}


	private function getCompiler(string $name, string $type): Compiler
	{
		/** @var Compiler $compiler */
		$compiler = $this->diContainer->getService(
			$this->extensionName .
			'.' .
			$type .
			ucfirst($name) .
			'Compiler'
		);
		return $compiler;
	}


	public function createCssLoader(string $name, bool $appendLastModified = true): CssLoader
	{
		$compiler = $this->getCompiler($name, 'css');
		$this->modifyConvention($compiler->getOutputNamingConvention(), $name);
		return new CssLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}


	public function createJavaScriptLoader(string $name, bool $appendLastModified = true): JavaScriptLoader
	{
		$compiler = $this->getCompiler($name, 'js');
		$this->modifyConvention($compiler->getOutputNamingConvention(), $name);
		return new JavaScriptLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}


	private function formatTempPath(string $name, bool $absoluteUrl = false): string
	{
		$lName = strtolower($name);
		$tempPath = $this->tempPaths[$lName] ?? Extension::DEFAULT_TEMP_PATH;
		$method = $absoluteUrl ? 'getBaseUrl' : 'getBasePath';
		return rtrim($this->httpRequest->getUrl()->{$method}(), '/') . '/' . $tempPath;
	}


	private function modifyConvention(IOutputNamingConvention $convention, string $name): void
	{
		if ($convention instanceof DefaultOutputNamingConvention) {
			$convention->setPrefix($name . '-');
		}
	}
}
