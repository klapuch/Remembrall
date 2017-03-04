<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Uri;

interface Pages {
	/**
	 * Add a new page
	 * @param \Klapuch\Uri\Uri $uri
	 * @param \Remembrall\Model\Web\Page $page
	 * @return \Remembrall\Model\Web\Page
	 */
	public function add(Uri\Uri $uri, Page $page): Page;
}