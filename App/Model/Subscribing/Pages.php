<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Uri;

interface Pages {
	/**
	 * Add a new page
	 * @param \Klapuch\Uri\Uri $uri
	 * @param \Remembrall\Model\Subscribing\Page $page
	 * @return \Remembrall\Model\Subscribing\Page
	 */
	public function add(Uri\Uri $uri, Page $page): Page;
}