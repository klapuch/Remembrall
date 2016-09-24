<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;
use Remembrall\Exception\NotFoundException;
use Klapuch\{
    Output, Uri
};

/**
 * Part which will always exists
 */
final class ExistingPart implements Part {
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
		if(!$this->exists())
			throw new NotFoundException('The part does not exist');
		return $this->origin->content();
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
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM parts
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id]
		);
	}
}
