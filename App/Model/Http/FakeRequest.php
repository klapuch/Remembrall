<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeRequest implements Request {
	private $page;

	public function __construct(Subscribing\Page $page = null) {
	    $this->page = $page;
	}

	public function send(): Subscribing\Page {
		return $this->page;
	}
}