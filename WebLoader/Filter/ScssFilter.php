<?php

declare(strict_types = 1);

namespace WebLoader\Filter;

use ScssPhp\ScssPhp\Compiler as ScssCompiler;
use WebLoader\Compiler;

/**
 * Scss CSS filter
 *
 * @author Roman Matěna
 * @license MIT
 */
class ScssFilter
{

	/** @var ScssCompiler|null */
	private $sc;


	public function __construct(?ScssCompiler $sc = null)
	{
		$this->sc = $sc;
	}


	private function getScssC(): ScssCompiler
	{
		// lazy loading
		if (empty($this->sc)) {
			$this->sc = new ScssCompiler();
		}

		return $this->sc;
	}


	public function __invoke(string $code, Compiler $loader, string $file): string
	{
		$file = (string) $file;

		if (pathinfo($file, PATHINFO_EXTENSION) === 'scss') {
			$this->getScssC()->setImportPaths(['', pathinfo($file, PATHINFO_DIRNAME) . '/']);
			return $this->getScssC()->compile($code);
		}

		return (string) $code;
	}
}
