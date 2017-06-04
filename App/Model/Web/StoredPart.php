<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Part stored in the database
 */
final class StoredPart implements Part {
	private $origin;
	private $id;
	private $database;

	public function __construct(Part $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function content(): string {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT content
			FROM parts
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		))->field();
	}

	public function snapshot(): string {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT snapshot
			FROM parts
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		))->field();
	}

	public function refresh(): Part {
		$refreshedPart = $this->origin->refresh();
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE parts
			SET content = ?, snapshot = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$refreshedPart->content(), $refreshedPart->snapshot(), $this->id]
		))->execute();
		return $this;
	}

	public function print(Output\Format $format): Output\Format {
		$part = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url AS url, expression, content, snapshot, language
			FROM parts
			WHERE id = ?',
			[$this->id]
		))->row();
		return new Output\FilledFormat($format, $part);
	}
}