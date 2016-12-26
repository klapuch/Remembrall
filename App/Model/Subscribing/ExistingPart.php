<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;
use Remembrall\Exception\NotFoundException;

/**
 * Part which will always exists
 */
final class ExistingPart implements Part {
	private $origin;
	private $id;
	private $database;

	public function __construct(Part $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function content(): string {
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->content();
	}

	public function snapshot(): string {
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->snapshot();
	}

	public function refresh(): Part {
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->refresh();
	}

	/**
	 * Does the part really exist?
	 * @return bool
	 */
	private function exists(): bool {
		return (bool)(new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM parts
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		))->field();
	}
}