<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Output;

/**
 * Fake
 */
final class FakePart implements Part {
	private $content;
	private $refreshedPart;
	private $snapshot;

	public function __construct(
		string $content = null,
		self $refreshedPart = null,
		string $snapshot = null
	) {
		$this->content = $content;
		$this->refreshedPart = $refreshedPart;
		$this->snapshot = $snapshot;
	}

	public function content(): string {
		return $this->content;
	}

	public function snapshot(): string {
		return $this->snapshot;
	}

	public function refresh(): Part {
		return $this->refreshedPart ?? $this;
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}