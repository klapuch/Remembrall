<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Gajus\Dindent;
use Klapuch\Dataset;
use Klapuch\Iterator;
use Klapuch\Uri;
use Texy;

/**
 * Formatted parts
 */
final class FormattedParts implements Parts {
	private $origin;
	private $texy;
	private $indenter;

	public function __construct(
		Parts $origin,
		Texy\Texy $texy,
		Dindent\Indenter $indenter
	) {
		$this->origin = $origin;
		$this->texy = $texy;
		$this->indenter = $indenter;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->origin->add($part, $uri, $expression);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		return new Iterator\MappedIterator(
			$this->origin->all($selection),
			function(Part $part): Part {
				return new FormattedPart($part, $this->texy, $this->indenter);
			}
		);
	}

	public function count(): int {
		return $this->origin->count();
	}
}