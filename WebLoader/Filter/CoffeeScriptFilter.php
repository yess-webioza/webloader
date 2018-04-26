<?php

declare(strict_types = 1);

namespace WebLoader\Filter;

use WebLoader\Compiler;

/**
 * Coffee script filter
 *
 * @author Patrik Votoček
 * @license MIT
 */
class CoffeeScriptFilter
{

	/** @var \WebLoader\Filter\path to coffee bin */
	private $bin;

	/** @var bool */
	public $bare = false;

	public function __construct(string $bin = 'coffee')
	{
		$this->bin = $bin;
	}

	/**
	 * Invoke filter
	 *
	 * @param string
	 * @param \WebLoader\Compiler
	 * @param string
	 * @return string
	 */
	public function __invoke(string $code, Compiler $loader, ?string $file = null): string
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'coffee') {
			$code = $this->compileCoffee($code);
		}

		return $code;
	}

	public function compileCoffee(string $source, ?bool $bare = null): string
	{
		if (is_null($bare)) {
			$bare = $this->bare;
		}

		$cmd = $this->bin . ' -p -s' . ($bare ? ' -b' : '');

		return Process::run($cmd, $source);
	}

}
