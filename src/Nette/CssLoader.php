<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use WebLoader\File;

/**
 * Css loader
 *
 * @author Jan Marek
 * @license MIT
 */
class CssLoader extends WebLoader
{
	private ?string $media = null;
	private ?string $title = null;
	private string $type = 'text/css';
	private bool $alternate = false;


	public function getMedia(): string
	{
		return $this->media;
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function getTitle(): string
	{
		return $this->title;
	}


	public function isAlternate(): bool
	{
		return $this->alternate;
	}


	public function setMedia(string $media): self
	{
		$this->media = $media;
		return $this;
	}


	public function setType(string $type): self
	{
		$this->type = $type;
		return $this;
	}


	public function setTitle(string $title): self
	{
		$this->title = $title;
		return $this;
	}


	public function setAlternate(bool $alternate): self
	{
		$this->alternate = $alternate;
		return $this;
	}


	public function getElement(File $file): Html
	{
		if ($this->alternate) {
			$alternate = ' alternate';
		} else {
			$alternate = '';
		}

		$el = Html::el('link');
		$el->setAttribute('rel', 'stylesheet' . $alternate);
		$el->setAttribute('type', $this->type);
		$el->setAttribute('media', $this->media);
		$el->setAttribute('title', $this->title);
		$el->setAttribute('nonce', $this->getCompiler()->getNonce());
		$el->setAttribute('href', $this->getGeneratedFilePath($file));

		return $el;
	}


	public function getInlineElement(File $file): Html
	{
		$el = Html::el('style');
		$el->setAttribute('type', $this->type);
		$el->setAttribute('media', $this->media);
		$el->setAttribute('title', $this->title);
		$el->setAttribute('nonce', $this->getCompiler()->getNonce());
		$el->setHtml(FileSystem::read($file->getPath()));

		return $el;
	}

}
