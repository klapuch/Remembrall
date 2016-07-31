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
		$refreshedPart = $this->origin->refresh();
		$this->database->query(
			'UPDATE parts
			SET content = ?, content_hash = MD5(?)
			WHERE page_url = ? AND expression = ?',
			$refreshedPart->content(), //todo
			$refreshedPart->content(),
			$this->url,
			$this->expression
		);
		return $this;
	}

	public function print(): array {
		return $this->origin->print() + [
			'url' => $this->url,
			'expression' => $this->expression,
		];
	}
}