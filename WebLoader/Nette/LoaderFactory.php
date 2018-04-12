<?php

namespace WebLoader\Nette;

use Nette\DI\Container;
use Nette\Http\IRequest;
use WebLoader\Compiler;

class LoaderFactory
{

	/** @var IRequest */
	private $httpRequest;

	/** @var Container */
	private $serviceLocator;

	/** @var array */
	private $tempPaths;

	/** @var string */
	private $extensionName;

	/**
	 * @param array $tempPaths
	 * @param string $extensionName
	 * @param IRequest $httpRequest
	 * @param Container $serviceLocator
	 */
	public function __construct(array $tempPaths, $extensionName, IRequest $httpRequest, Container $serviceLocator)
	{
		$this->httpRequest = $httpRequest;
		$this->serviceLocator = $serviceLocator;
		$this->tempPaths = $tempPaths;
		$this->extensionName = $extensionName;
	}

	/**
	 * @param string $name
	 * @param bool $appendLastModified
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createCssLoader($name, $appendLastModified = FALSE)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService($this->extensionName . '.css' . ucfirst($name) . 'Compiler');
		return new CssLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}

	/**
	 * @param string $name
	 * @param bool $appendLastModified
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createJavaScriptLoader($name, $appendLastModified = FALSE)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService($this->extensionName . '.js' . ucfirst($name) . 'Compiler');
		return new JavaScriptLoader($compiler, $this->formatTempPath($name, $compiler->isAbsoluteUrl()), $appendLastModified);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function formatTempPath($name, $absoluteUrl = FALSE)
	{
		$lName = strtolower($name);
		$tempPath = isset($this->tempPaths[$lName]) ? $this->tempPaths[$lName] : Extension::DEFAULT_TEMP_PATH;
		$method = $absoluteUrl ? 'getBaseUrl' : 'getBasePath';
		return rtrim($this->httpRequest->getUrl()->{$method}(), '/') . '/' . $tempPath;
	}

}
