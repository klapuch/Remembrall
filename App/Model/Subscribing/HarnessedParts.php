<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Uri;
use Remembrall\Model\Misc;

/**
 * Parts harnessed by callback
 */
final class HarnessedParts implements Parts {
	private $origin;
	private $callback;

	public function __construct(Parts $origin, Misc\Callback $callback) {
		$this->origin = $origin;
		$this->callback = $callback;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): void {
		$this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}

	public function iterate(Dataset\Selection $selection): \Traversable {
		return $this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}

	public function count(): int {
		return $this->callback->invoke(
			[$this->origin, __FUNCTION__],
			func_get_args()
		);
	}
}