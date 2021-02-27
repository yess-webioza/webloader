<?php

declare(strict_types = 1);

namespace WebLoader;

class File
{

	protected string $file;
	protected ?int $lastModified;
	protected array $sourceFiles;


	public function __construct(
		string $file,
		?int $lastModified,
		array $sourceFiles
	) {
		$this->file = $file;
		$this->lastModified = $lastModified;
		$this->sourceFiles = $sourceFiles;
	}


	public function getFile(): string
	{
		return $this->file;
	}


	public function getLastModified(): ?int
	{
		return $this->lastModified;
	}


	public function getSourceFiles(): array
	{
		return $this->sourceFiles;
	}
}
