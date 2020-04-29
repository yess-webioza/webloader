<?php
declare(strict_types=1);

namespace WebLoader\Filter;

use tubalmartin\CssMin\Minifier;
use WebLoader\Compiler;

class CssMinFilter
{

	/**
	 * Minify code
	 */
	public function __invoke(string $code, Compiler $compiler, string $file = ''): string
	{
		$minifier = new Minifier;
		return $minifier->run($code);
	}
}
