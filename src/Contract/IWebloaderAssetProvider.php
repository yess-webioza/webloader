<?php
declare(strict_types = 1);

namespace WebLoader\Contract;

interface IWebloaderAssetProvider
{
	public function getWebloaderAssets(): array;
}
