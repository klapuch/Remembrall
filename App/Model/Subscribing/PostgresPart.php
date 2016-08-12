<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

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
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->url = $url;
		$this->expression = $expression;
		$this->database = $database;
	}

	public function content(): string {
		return $this->database->fetchColumn(
			'SELECT content
			FROM parts
			WHERE expression IS NOT DISTINCT FROM ?
			AND page_url IS NOT DISTINCT FROM ?',
			[$this->expression, $this->url]
		);
	}

	public function refresh(): Part {
		$refreshedPart = $this->origin->refresh();
		$this->database->query(
			'UPDATE parts
			SET content = ?
			WHERE page_url IS NOT DISTINCT FROM ? 
			AND expression IS NOT DISTINCT FROM ?',
			[$refreshedPart->content(), $this->url, $this->expression]
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