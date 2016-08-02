<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;

/**
 * Part which will always exists in the database
 */
final class ExistingPart implements Part {
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
		if(!$this->exists())
			throw new Exception\NotFoundException('The part does not exist');
		return $this->origin->content();
	}

	public function refresh(): Part {
		if(!$this->exists())
			throw new Exception\NotFoundException('The part does not exist');
		return $this->origin->refresh();
	}

	public function print(): array {
		return $this->origin->print() + [
			'url' => $this->url,
			'expression' => $this->expression,
		];
	}

	/**
	 * Does the part really exists?
	 * @return bool
	 */
	private function exists(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM parts
			WHERE page_url IS NOT DISTINCT FROM ?
			AND expression IS NOT DISTINCT FROM ?',
			$this->url,
			$this->expression
		);
	}
}