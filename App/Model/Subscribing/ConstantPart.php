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
	private $owner;
	private $visitedAt;

	public function __construct(
		Page $source,
		string $content,
		Expression $expression,
		Access\Subscriber $owner,
		Interval $visitedAt
	) {
		$this->source = $source;
		$this->content = $content;
		$this->expression = $expression;
		$this->owner = $owner;
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

	public function owner(): Access\Subscriber {
		return $this->owner;
	}

	public function visitedAt(): Interval {
		return $this->visitedAt;
	}
}