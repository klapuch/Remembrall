<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakePart implements Part {
	private $source;
	private $content;

	public function __construct(string $content = null, Page $source = null) {
		$this->source = $source;
		$this->content = $content;
	}

	public function source(): Page {
		return $this->source;
	}

	public function content(): string {
		return $this->content;
	}
}