<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

/**
 * Constant part without roundtrips
 */
final class ConstantPart implements Part {
	private $source;
	private $content;
	private $expression;
	private $visitedAt;

	public function __construct(
		Page $source,
		string $content,
		Expression $expression,
		Interval $visitedAt
	) {
		$this->source = $source;
		$this->content = $content;
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
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}

	public function expression(): Expression {
		return $this->expression;
	}

	public function visitedAt(): Interval {
		return $this->visitedAt;
	}
}