<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePages implements Pages {
	private $pages;

	public function __construct(array $pages = []) {
		$this->pages = $pages;
	}

	public function add(Page $page): Page {
		return $page;
	}

	public function iterate(): array {
		return $this->pages;
	}

	public function replace(Page $old, Page $new) {
		
	}
}