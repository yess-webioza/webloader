<?php

declare(strict_types = 1);

namespace WebLoader;

/**
 * DefaultNamingConvention
 *
 * @author Jan Marek
 */
class DefaultOutputNamingConvention implements \WebLoader\IOutputNamingConvention
{

	/** @var string */
	private $prefix = '';

	/** @var string */
	private $suffix = '';

	public static function createCssConvention(): DefaultOutputNamingConvention
	{
		$convention = new static();
		$convention->setSuffix('.css');

		return $convention;
	}

	public static function createJsConvention(): DefaultOutputNamingConvention
	{
		$convention = new static();
		$convention->setSuffix('.js');

		return $convention;
	}

	/**
	 * Get generated file name prefix
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/**
	 * Set generated file name prefix
	 * @param string $prefix generated file name prefix
	 */
	public function setPrefix(string $prefix): void
	{
		$this->prefix = (string) $prefix;
	}


	/**
	 * Get generated file name suffix
	 * @return string
	 */
	public function getSuffix(): string
	{
		return $this->suffix;
	}


	/**
	 * Set generated file name suffix
	 * @param string $suffix generated file name suffix
	 */
	public function setSuffix(string $suffix): void
	{
		$this->suffix = (string) $suffix;
	}

	/**
	 * Filename of generated file
	 * @param array $files
	 * @param \WebLoader\Compiler $compiler
	 * @return string
	 */
	public function getFilename(array $files, Compiler $compiler): string
	{
		return $this->prefix . $this->createHash($files, $compiler) . $this->suffix;
	}

	protected function createHash(array $files, Compiler $compiler)
	{
		$parts = $files;
		foreach ($files as $file) {
			$parts[] = @filemtime($file);
		}

		return substr(md5(implode('|', $parts)), 0, 12);
	}

}
