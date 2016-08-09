<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

interface Page {
	/**
	 * Content of the page
	 * @throws NotFoundException
	 * @return \DOMDocument
	 */
	public function content(): \DOMDocument;

	/**
	 * Refreshed page
	 * @return Page
	 */
	public function refresh(): Page; //todo because of mockery, should be self
}