<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

/**
 * Part stored in the Postgres database
 */
final class PostgresPart implements Part {
	private $origin;
	private $id;
	private $database;

	public function __construct(
		Part $origin,
		int $id,
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function content(): string {
		return $this->database->fetchColumn(
			'SELECT content
			FROM parts
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		);
	}

	public function refresh(): Part {
		$refreshedPart = $this->origin->refresh();
		$this->database->query(
			'UPDATE parts
			SET content = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$refreshedPart->content(), $this->id]
		);
		return $this;
	}
}