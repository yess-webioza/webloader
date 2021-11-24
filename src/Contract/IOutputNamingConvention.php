<?php

declare(strict_types = 1);

namespace WebLoader\Contract;

use WebLoader\Compiler;

/**
 * IOutputNamingConvention
 *
 * @author Jan Marek
 */
interface IOutputNamingConvention
{
	public function getFilename(array $files, Compiler $compiler): string;
}
