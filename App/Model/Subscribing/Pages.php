<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Pages {
	/**
	 * Add a new page to the pages
	 * @param Page $page
	 * @return void
	 */
	public function add(Page $page);

	/**
	 * Go through all the pages
	 * @return Page[]
	 */
	public function iterate(): array;
}