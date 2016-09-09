<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Output, Uri
};

/**
 * Part stored in the PostgreSQL database
 */
final class PostgresPart implements Part {
	private $origin;
	private $url;
	private $expression;
	private $database;

	public function __construct(
		Part $origin,
		Uri\Uri $url,
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
			[$this->expression, $this->url->reference()]
		);
	}

	public function refresh(): Part {
		$refreshedPart = $this->origin->refresh();
		$this->database->query(
			'UPDATE parts
			SET content = ?
			WHERE page_url IS NOT DISTINCT FROM ? 
			AND expression IS NOT DISTINCT FROM ?',
			[$refreshedPart->content(), $this->url->reference(), $this->expression]
		);
		return $this;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
		->with('url', $this->url->reference())
		->with('expression', $this->expression);
	}
}
