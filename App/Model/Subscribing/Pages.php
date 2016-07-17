<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Pages {
	/**
	 * Add a new page
	 * @param Page $page
	 * @return Page
	 */
	public function add(Page $page): Page;

	/**
	 * Go through all the pages
	 * @return Page[]
	 */
	public function iterate(): array;
}