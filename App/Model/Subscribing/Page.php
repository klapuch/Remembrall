<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Page {
	/**
	 * Content of the page
	 * @throws \Remembrall\Exception\NotFoundException
	 * @return \DOMDocument
	 */
	public function content(): \DOMDocument;

	/**
	 * Refreshed page
	 * @return \Remembrall\Model\Subscribing\Page
	 */
	public function refresh(): Page;
}