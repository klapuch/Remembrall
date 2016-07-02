<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

/**
 * Part on the html page (in the html format)
 */
final class HtmlPart implements Part {
	private $page;
	private $expression;
	private $owner;

	public function __construct(
		Page $page,
		Expression $expression,
		Access\Subscriber $owner
	) {
		$this->page = $page;
		$this->expression = $expression;
		$this->owner = $owner;
	}

	public function source(): Page {
		return $this->page;
	}

	public function content(): string {
		return (string)array_reduce(
			iterator_to_array($this->expression->match()),
			function($previous, \DOMNode $node) {
				$previous .= preg_replace(
					'~[\t\r\n]+~', // removes tabs and new lines (CR and LF)
					'',
					$node->ownerDocument->saveHTML($node)
				);
				return $previous;
			}
		);
	}

	public function equals(Part $part): bool {
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}

	public function expression(): Expression {
		return $this->expression;
	}

	public function owner(): Access\Subscriber {
		return $this->owner;
	}

	/**
	 * It is not possible to ensure when was the part visited
	 * @return Interval
	 */
	public function visitedAt(): Interval {
		return new DateTimeInterval(
			new \DateTimeImmutable(),
			new \DateInterval('PT0S')
		);
	}
}