<?php

namespace WebLoader\Test;

use WebLoader\BatchCollection;
use PHPUnit\Framework\TestCase;
use WebLoader\Exception\BatchAlreadyExistsException;

class BatchCollectionTest extends TestCase
{
	private BatchCollection $batchCollection;


	protected function setUp(): void
	{
		$this->batchCollection = new BatchCollection();
	}


	public function testAddGetBatches(): void
	{
		$this->batchCollection->addBatch('css', 'front.screen', []);
		$this->batchCollection->addBatch('js', 'front.head', []);

		$expected = [
			'css' => [
				'front.screen' => [],
			],
			'js' => [
				'front.head' => [],
			],
		];

		$this->assertSame($expected, $this->batchCollection->getBatches());
	}


	public function testAddBatchException(): void
	{
		$this->expectException(BatchAlreadyExistsException::class);
		$this->batchCollection->addBatch('css', 'front.screen', []);
		$this->batchCollection->addBatch('css', 'front.screen', []);
	}
}
