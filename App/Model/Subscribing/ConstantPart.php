<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Constant part without roundtrips
 */
final class ConstantPart implements Part {
	private $origin;
	private $content;
	private $page;
	private $interval;

	public function __construct(
		Part $origin,
		string $content,
		Page $page,
		Interval $interval
	) {
		$this->origin = $origin;
		$this->content = $content;
		$this->page = $page;
		$this->interval = $interval;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $this->origin->equals($part);
	}

	public function refresh(): Part {
		return $this->origin->refresh();
	}

	public function print(): array {
		return $this->origin->print() + [
			'interval' => $this->interval,
			'page' => $this->page,
		];
	}
}