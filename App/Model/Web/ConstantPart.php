<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Output;

final class ConstantPart implements Part {
	private $origin;
	private $content;
	private $snapshot;
	private $part;

	public function __construct(
		Part $origin,
		string $content,
		string $snapshot,
		array $part
	) {
		$this->origin = $origin;
		$this->content = $content;
		$this->snapshot = $snapshot;
		$this->part = $part;
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

	public function print(Output\Format $format): Output\Format {
		return new Output\FilledFormat($format, $this->part);
	}
}