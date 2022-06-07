<?php
declare(strict_types=1);

namespace WebLoader\Contract;

interface IBatchCollection
{
	public function getBatches(): array;

	public function addBatch(string $type, string $name, array $batch): void;
}
