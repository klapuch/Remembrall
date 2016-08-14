<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;
use Remembrall\Exception\NotFoundException;
use Klapuch\Output;

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
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->url = $url;
		$this->expression = $expression;
		$this->database = $database;
	}

	public function content(): string {
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->content();
	}

	public function refresh(): Part {
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->refresh();
	}

	public function print(Output\Format $format): Output\Format {
		$format->with('url', $this->url)
			->with('expression', $this->expression);
	}

	/**
	 * Does the part really exists?
	 * @return bool
	 */
	private function exists(): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM parts
			WHERE page_url IS NOT DISTINCT FROM ?
			AND expression IS NOT DISTINCT FROM ?',
			[$this->url, $this->expression]
		);
	}
}