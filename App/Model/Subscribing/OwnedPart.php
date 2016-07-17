<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Access;
use Remembrall\Exception;

final class OwnedPart implements Part {
	private $origin;
	private $expression;
	private $owner;
	private $page;
	private $database;

	public function __construct(
		Part $origin,
		Dibi\Connection $database,
		Expression $expression,
		Access\Subscriber $owner,
		Page $page
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->expression = $expression;
		$this->owner = $owner;
		$this->page = $page;
	}

	public function content(): string {
		if(!$this->owned())
			throw new Exception\NotFoundException('You do not own this part');
		return $this->database->fetchSingle(
			'SELECT content
			FROM parts
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_url = ?',
			$this->owner->id(),
			(string)$this->expression,
			$this->page->url()
		);
	}

	public function refresh(): Part {
		if(!$this->owned())
			throw new Exception\NotFoundException('You do not own this part');
		return $this->origin->refresh();
	}

	public function print(): array {
		return $this->origin->print() + [
			'expression' => $this->expression,
			'page' => $this->page,
			'subscriber' => $this->owner,
		];
	}

	public function equals(Part $part): bool {
		return $this->content() === $part->content();
	}

	/**
	 * Is the part owned by the given owner?
	 * @return bool
	 */
	private function owned(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM subscribed_parts
			WHERE subscriber_id = ?
			AND part_id = (
				SELECT ID
				FROM parts
				WHERE expression = ? AND page_url = ?
			)',
			$this->owner->id(),
			(string)$this->expression,
			$this->page->url()
		);
	}
}