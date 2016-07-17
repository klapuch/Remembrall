<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePart implements Part {
	private $content;
	private $equals;
	private $page;
	private $expression;

	public function __construct(
		string $content = null,
		bool $equals = false,
		Page $page = null,
		Expression $expression = null
	) {
		$this->content = $content;
		$this->equals = $equals;
		$this->page = $page;
		$this->expression = $expression;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $this->equals;
	}

	public function print(): array {
		return [
			'page' => $this->page,
			'expression' => $this->expression,
		];
	}
}