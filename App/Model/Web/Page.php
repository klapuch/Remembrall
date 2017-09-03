<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

interface Page {
	/**
	 * Content of the page
	 * @throws \UnexpectedValueException
	 * @return \DOMDocument
	 */
	public function content(): \DOMDocument;

	/**
	 * Refreshed page
	 * @return \Remembrall\Model\Web\Page
	 */
	public function refresh(): Page;
}