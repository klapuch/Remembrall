<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Page {
	/**
	 * Content of the page
	 * @throws Exception\ExistenceException
	 * @return \DOMDocument
	 */
	public function content(): \DOMDocument;

	/**
	 * Url of the page
	 * @return string
	 */
	public function url(): string;

	/**
	 * Is the given page equals to the current one?
	 * @param Page $page
	 * @return bool
	 */
	public function equals(self $page): bool;
}