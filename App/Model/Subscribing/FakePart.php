<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

final class FakePart implements Part {
	private $source;
	private $content;
	private $equals;
	private $expression;
	private $owner;
	private $visitedAt;

	public function __construct(
		string $content = null,
		Page $source = null,
		bool $equals = false,
		Expression $expression = null,
		Access\Subscriber $owner = null,
		Interval $visitedAt = null
	) {
		$this->source = $source;
		$this->content = $content;
		$this->equals = $equals;
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
		return $this->equals;
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