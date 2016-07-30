<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePart implements Part {
	private $content;
	private $url;
	private $refreshedPart;
	private $expression;
	private $page;

	public function __construct(
		string $content = null,
		string $url = null,
		self $refreshedPart = null,
		Expression $expression = null,
		Page $page = null
	) {
		$this->content = $content;
		$this->url = $url;
		$this->refreshedPart = $refreshedPart;
		$this->expression = $expression;
		$this->page = $page;
	}

	public function content(): string {
		return $this->content;
	}

	public function refresh(): Part {
		return $this->refreshedPart ?? $this;
	}

	public function print(): array {
		return [
			'url' => $this->url,
			'expression' => $this->expression,
			'page' => $this->page,
		];
	}
}