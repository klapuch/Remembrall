<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Pages {
	/**
	 * Add a new page to the pages
	 * @param Page $page
	 * @return Page
	 */
	public function add(Page $page): Page;

	/**
	 * Replace the old page with the new one
	 * @param Page $old
	 * @param Page $new
	 * @return void
	 */
	public function replace(Page $old, Page $new);

	/**
	 * Go through all the pages
	 * @return Page[]
	 */
	public function iterate(): array;
}