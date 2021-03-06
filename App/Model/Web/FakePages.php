<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Uri;

final class FakePages implements Pages {
	public function add(Uri\Uri $uri, Page $page): Page {
		return new FakePage();
	}
}