<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Uri;

/**
 * Safe parts taking care of common exceptions
 */
final class SafeParts implements Parts {
	private const INVALID_TEXT_REPRESENTATION = '22P02';
	private $origin;
	private $database;

	public function __construct(Parts $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression, string $language): void {
		try {
			$this->origin->add($part, $url, $expression, $language);
		} catch (\PDOException $ex) {
			if ($ex->getCode() === self::INVALID_TEXT_REPRESENTATION) {
				throw new \UnexpectedValueException(
					sprintf(
						'Allowed languages are "%s" - "%s" given',
						(new Storage\ParameterizedQuery(
							$this->database,
							"SELECT array_to_string(enum_range(NULL::languages), ', ')"
						))->field(),
						$language
					),
					0,
					$ex
				);
			}
			throw $ex;
		}
	}

	public function all(Dataset\Selection $selection): \Traversable {
		return $this->origin->all($selection);
	}

	public function count(): int {
		return $this->origin->count();
	}
}