<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

/**
 * Fake
 */
final class FakePart implements Part {
	private $source;
	private $content;
	private $equals;
	private $expression;
	private $visitedAt;

	public function __construct(
		Page $source = null,
		Expression $expression = null,
		string $content = null,
		bool $equals = false,
		Interval $visitedAt = null
	) {
		$this->source = $source;
		$this->content = $content;
		$this->equals = $equals;
		$this->expression = $expression;
		$this->visitedAt = $visitedAt;
	}

	public function source(): Page {
		return $this->source;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $this->equals;
	}

	public function expression(): Expression {
		return $this->expression;
	}

	public function visitedAt(): Interval {
		return $this->visitedAt;
	}
}