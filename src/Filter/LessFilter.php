<?php
declare(strict_types=1);

namespace WebLoader\Filter;

use Less_Parser;
use Nette\SmartObject;
use WebLoader\Compiler;

/**
 * Less CSS filter
 *
 * @author Jan Marek
 * @license MIT
 */
class LessFilter
{
	use SmartObject;

	private function getLessParser(): Less_Parser
	{
		return new Less_Parser;
	}


	public function __invoke(string $code, Compiler $loader, string $file): string
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'less') {
			$parser = $this->getLessParser();
			$parser->parseFile($file);
			return $parser->getCss();
		}

		return $code;
	}
}
