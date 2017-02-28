<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Gajus\Dindent;
use Klapuch\Output;
use Texy;

final class FormattedPart implements Part {
	private $origin;
	private $texy;
	private $indenter;

	public function __construct(
		Part $origin,
		Texy\Texy $texy,
		Dindent\Indenter $indenter
	) {
		$this->origin = $origin;
		$this->texy = $texy;
		$this->indenter = $indenter;
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
					sprintf(
						"/---code html \n %s",
						$this->indenter->indent($content)
					)
				);
			});
	}
}