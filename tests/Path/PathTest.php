<?php
declare(strict_types=1);

namespace WebLoader\Test\Path;

use PHPUnit\Framework\TestCase;
use WebLoader\Path;

class PathTest extends TestCase
{
	public function testNormalize(): void
	{
		$normalized = Path::normalize('/path/to//project//that/contains/0/in/it');
		$this->assertEquals('/path/to/project/that/contains/0/in/it', $normalized);
	}


	public function testDirectoryJump(): void
	{
		$normalized = Path::normalize('/path/to/my/project/../../wrong/path/../../correct/one/');
		$this->assertEquals('/path/to/correct/one/', $normalized);
	}
}
