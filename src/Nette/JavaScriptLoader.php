<?php

declare(strict_types = 1);

namespace WebLoader\Nette;

use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use WebLoader\File;

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader
{
	public function getElement(File $file): Html
	{
		$el = Html::el('script');
		$el->setAttribute('async', $this->getCompiler()->isAsync());
		$el->setAttribute('defer', $this->getCompiler()->isDefer());
		$el->setAttribute('nonce', $this->getCompiler()->getNonce());
		$el->setAttribute('src', $this->getGeneratedFilePath($file));

		return $el;
	}


	public function getInlineElement(File $file): Html
	{
		$el = Html::el('script');
		$el->setAttribute('nonce', $this->getCompiler()->getNonce());
		$el->setHtml(FileSystem::read($file->getPath()));

		return $el;
	}

}
