<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\Utils\Html;

/**
 * Css loader
 *
 * @author Jan Marek
 * @license MIT
 */
class CssLoader extends \WebLoader\Nette\WebLoader
{

	/** @var string */
	private $media;

	/** @var string */
	private $title;

	/** @var string */
	private $type = 'text/css';

	/** @var bool */
	private $alternate = false;

	/**
	 * Get media
	 * @return string
	 */
	public function getMedia(): string
	{
		return $this->media;
	}

	/**
	 * Get type
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get title
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * Is alternate ?
	 * @return bool
	 */
	public function isAlternate(): bool
	{
		return $this->alternate;
	}

	/**
	 * Set media
	 * @param string $media
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function setMedia(string $media): CssLoader
	{
		$this->media = $media;
		return $this;
	}

	/**
	 * Set type
	 * @param string $type
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function setType(string $type): CssLoader
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set title
	 * @param string $title
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function setTitle(string $title): CssLoader
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Set alternate
	 * @param bool $alternate
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function setAlternate(bool $alternate): CssLoader
	{
		$this->alternate = $alternate;
		return $this;
	}

	/**
	 * Get link element
	 * @param string $source
	 * @return \Nette\Utils\Html
	 */
	public function getElement(string $source): Html
	{
		if ($this->alternate) {
			$alternate = ' alternate';
		} else {
			$alternate = '';
		}

		return Html::el('link')->rel('stylesheet' . $alternate)->type($this->type)->media($this->media)->title($this->title)->href($source);
	}

}
