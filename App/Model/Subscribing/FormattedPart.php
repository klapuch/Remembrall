<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Texy\Texy;

final class FormattedPart implements Part {
	private $origin;
	private $texy;

	public function __construct(Part $origin, Texy $texy) {
		$this->origin = $origin;
		$this->texy = $texy;
	}

	public function content(): string {
		return $this->origin->content();
	}

	public function snapshot(): string {
		return $this->origin->snapshot();
	}

	public function refresh(): Part {
		return $this->origin->refresh();
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('content', function(string $content): string {
				return $this->texy->process(
					sprintf("/---code html \n %s", $content)
				);
			});
	}
}