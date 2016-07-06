<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Access;

final class OwnedPart implements Part {
	private $expression;
	private $owner;
	private $source;
	private $database;

	public function __construct(
		Dibi\Connection $database,
		Expression $expression,
		Access\Subscriber $owner,
		Page $source
	) {
	    $this->database = $database;
		$this->expression = $expression;
		$this->owner = $owner;
		$this->source = $source;
	}

	public function source(): Page {
		return $this->source;
	}

	public function content(): string {
		return $this->database->fetchSingle(
			'SELECT content
			FROM parts
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			$this->owner->id(),
			(string)$this->expression(),
			$this->source->url()
		);
	}

	public function equals(Part $part): bool {
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}

	public function expression(): Expression {
		return $this->expression;
	}

	public function visitedAt(): Interval {
		$interval = $this->database->fetch(
			'SELECT `interval`, visited_at
			FROM parts
			INNER JOIN part_visits ON part_visits.part_id = parts.ID
			WHERE subscriber_id = ?
			AND expression = ?
			AND page_id = (SELECT ID FROM pages WHERE url = ?)',
			$this->owner->id(),
			(string)$this->expression,
			$this->source()->url()
		);
		return new DateTimeInterval(
			new \DateTimeImmutable((string)$interval['visited_at']),
			new \DateInterval($interval['interval'])
		);
	}
}