<?php

declare(strict_types = 1);

namespace WebLoader;

use Nette\Utils\FileSystem;

class Path
{
	public static function normalize(string $path): string
	{
		return FileSystem::normalizePath($path);
	}
}
