<?php
declare(strict_types = 1);

namespace WebLoader\Contract;

interface IBatchProvider
{
	public function getBatches(): array;
}
