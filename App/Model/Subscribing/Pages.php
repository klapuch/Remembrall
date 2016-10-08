<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

interface Pages {
	/**
	 * Add a new page
	 * @param Uri\Uri $uri
	 * @param Page $page
	 * @return Page
	 */
	public function add(Uri\Uri $uri, Page $page): Page;
}