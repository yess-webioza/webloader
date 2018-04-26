<?php

declare(strict_types = 1);

namespace WebLoader\Filter;

use WebLoader\Compiler;

/**
 * Less CSS filter
 *
 * @author Jan Tvrdík
 * @license MIT
 */
class LessBinFilter
{

	/** @var string */
	private $bin;

	/** @var array */
	private $env;

	/**
	 * @param string $bin
	 * @param array $env
	 */
	public function __construct(string $bin = 'lessc', array $env = [])
	{
		$this->bin = $bin;
		$this->env = $env + $_ENV;
		unset($this->env['argv'], $this->env['argc']);
	}

	/**
	 * Invoke filter
	 * @param string $code
	 * @param \WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke(string $code, Compiler $loader, string $file): string
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'less') {
			$code = Process::run("{$this->bin} -", $code, dirname($file), $this->env);
		}

		return $code;
	}

}
