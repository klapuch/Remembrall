<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

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

	public function __construct(Parts $origin, Texy\Texy $texy) {
		$this->origin = $origin;
		$this->texy = $texy;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->origin->add($part, $uri, $expression);
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		return new Iterator\MappedIterator(
			$this->origin->iterate($selection),
			function(Part $part): Part {
				return new FormattedPart($part, $this->texy);
			}
		);
	}
}