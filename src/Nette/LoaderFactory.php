<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\DI\Container;
use Nette\Http\IRequest;
use WebLoader\Compiler;

class LoaderFactory
{

	/** @var IRequest */
	private $httpRequest;

	/** @var Container */
	private $diContainer;

	/** @var array<string> */
	private $tempPaths;

	/** @var string */
	private $extensionName;


	/**
	 * LoaderFactory constructor.
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


	public function createCssLoader(string $name, bool $appendLastModified = true): CssLoader
	{
		/** @var Compiler $compiler */
		$compiler = $this->diContainer->getService($this->extensionName . '.css' . ucfirst($name) . 'Compiler');
		return new CssLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}


	public function createJavaScriptLoader(string $name, bool $appendLastModified = true): JavaScriptLoader
	{
		/** @var Compiler $compiler */
		$compiler = $this->diContainer->getService($this->extensionName . '.js' . ucfirst($name) . 'Compiler');
		return new JavaScriptLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}


	private function formatTempPath(string $name, bool $absoluteUrl = false): string
	{
		$lName = strtolower($name);
		$tempPath = isset($this->tempPaths[$lName]) ? $this->tempPaths[$lName] : Extension::DEFAULT_TEMP_PATH;
		$method = $absoluteUrl ? 'getBaseUrl' : 'getBasePath';
		return rtrim($this->httpRequest->getUrl()->{$method}(), '/') . '/' . $tempPath;
	}
}
