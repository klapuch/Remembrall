<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakePages implements Pages {
	public function add(string $url, Page $page): Page {
		return $page;
	}

	public function iterate(): array {
	}
}
