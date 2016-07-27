<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

/**
 * Part stored in the Postgres database
 */
final class PostgresPart implements Part {
	private $origin;
	private $url;
	private $expression;
	private $database;

	public function __construct(
		Part $origin,
		string $url,
		string $expression,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->url = $url;
		$this->expression = $expression;
		$this->database = $database;
	}

	public function content(): string {
		return $this->database->fetchSingle(
			'SELECT content
			FROM parts
			WHERE expression = ?
			AND page_url = ?',
			$this->expression,
			$this->url
		);
	}

	public function refresh(): Part {
		$this->database->query(
			'UPDATE parts
			SET content = ?
			WHERE page_url = ? AND expression = ?',
			$this->origin->refresh()->content(),
			$this->url,
			$this->expression
		);
		return $this;
	}

	public function equals(Part $part): bool {
		return $this->content() === $part->content();
	}
}