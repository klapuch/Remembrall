<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakePart implements Part {
	private $source;
	private $content;
	private $equals;
	private $expression;

	public function __construct(
		string $content = null,
		Page $source = null,
		bool $equals = false,
		Expression $expression = null
	) {
		$this->source = $source;
		$this->content = $content;
		$this->equals = $equals;
		$this->expression = $expression;
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
}