<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Model\Subscribing;

final class FakeBrowser implements Browser {
	private $page;

	public function __construct(Subscribing\Page $page = null) {
	    $this->page = $page;
	}

	public function send(Request $request): Subscribing\Page {
		return $this->page;
	}
}