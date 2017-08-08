<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Uri;

/**
 * Popular or unreliable parts decided by type
 */
final class SuitedParts implements Parts {
	private const TYPES = ['popular', 'unreliable'];
	private $type;
	private $database;

	public function __construct(string $type, \PDO $database) {
		$this->type = $type;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression, string $language): void {
		$this->parts($this->type)->add($part, $uri, $expression, $language);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		return $this->parts($this->type)->all($selection);
	}

	public function count(): int {
		return $this->parts($this->type)->count();
	}

	/**
	 * Parts decided by type
	 */
	private function parts(string $type): Parts {
		if (in_array(strtolower($type), self::TYPES)) {
			if (strcasecmp($type, 'popular')) {
				return new PopularParts(
					new CollectiveParts($this->database),
					$this->database
				);
			} elseif (strcasecmp($type, 'unreliable')) {
				return new UnreliableParts(
					new CollectiveParts($this->database),
					$this->database
				);
			}
		}
		throw new \UnexpectedValueException(
			sprintf('Allowed types are %s', implode(', ', self::TYPES))
		);
	}
}