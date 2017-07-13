<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Gajus\Dindent;
use Texy;

/**
 * Code made pretty and secure
 */
final class PrettyCode {
	private $code;
	private $texy;
	private $indenter;

	public function __construct(
		string $code,
		Texy\Texy $texy,
		Dindent\Indenter $indenter
	) {
		$this->code = $code;
		$this->texy = $texy;
		$this->indenter = $indenter;
	}

	public function __toString(): string {
		return $this->texy->process(
			sprintf(
				"/---code html \n %s",
				$this->indenter->indent($this->code)
			)
		);
	}
}