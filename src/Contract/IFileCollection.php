<?php

declare(strict_types=1);

namespace WebLoader\Contract;

/**
 * @author Jan Marek
 */
interface IFileCollection
{
	public function getRoot(): string;

	public function getFiles(): array;

	public function getRemoteFiles(): array;

	public function getWatchFiles(): array;
}
