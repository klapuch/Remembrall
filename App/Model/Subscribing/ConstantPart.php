<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class ConstantPart implements Part {
	private $origin;
	private $content;
	private $snapshot;

	public function __construct(Part $origin, string $content, string $snapshot) {
		$this->origin = $origin;
		$this->content = $content;
		$this->snapshot = $snapshot;
	}

	public function content(): string {
		return $this->content;
	}

	public function snapshot(): string {
		return $this->snapshot;
	}

	public function refresh(): Part {
		return $this->origin->refresh();
	}
}