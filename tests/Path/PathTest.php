<?php

namespace WebLoader\Test\Path;

use PHPUnit\Framework\TestCase;
use WebLoader\Path;

class PathTest extends TestCase
{

	public function testNormalize()
	{
		$normalized = Path::normalize('/path/to//project//that/contains/0/in/it');
		$this->assertEquals('/path/to/project/that/contains/0/in/it', $normalized);
	}

}
