<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Output;
use Klapuch\Storage;

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
		if ($this->exists())
			return $this->origin->content();
		throw new \UnexpectedValueException(
			sprintf(
				'Content from part id "%d" does not exist',
				$this->id
			)
		);
	}

	public function snapshot(): string {
		if ($this->exists())
			return $this->origin->snapshot();
		throw new \UnexpectedValueException(
			sprintf(
				'Snapshot from part id "%d" does not exist',
				$this->id
			)
		);
	}

	public function refresh(): Part {
		if ($this->exists())
			return $this->origin->refresh();
		throw new \UnexpectedValueException(
			sprintf(
				'The part id "%d" does not exist',
				$this->id
			)
		);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}

	/**
	 * Does the part really exist?
	 * @return bool
	 */
	private function exists(): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM parts
			WHERE id = ?',
			[$this->id]
		))->field();
	}
}