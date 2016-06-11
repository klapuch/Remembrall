<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

final class FakePage implements Page {
	private $url;
	private $content;

	public function __construct(
		string $url = null,
		\DOMDocument $content = null
	) {
		$this->url = $url;
		$this->content = $content;
	}

	public function content(): \DOMDocument {
		return $this->content;
	}

	public function url(): string {
		return $this->url;
	}
}