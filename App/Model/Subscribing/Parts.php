<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Uri, Output
};

abstract class Parts implements \IteratorAggregate {
	/**
	 * Add a new part to the parts
	 * @param Part $part
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @return void
	 */
	abstract public function add(Part $part, Uri\Uri $uri, string $expression): void;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format[]
	 */
	public function print(Output\Format $format): array {
		return array_map(
			function(array $part) use ($format): Output\Format {
				return $format->with('id', $part['id'])
					->with('url', $part['url'])
					->with('expression', $part['expression'])
					->with('content', $part['content']);
			},
			$this->rows()
		);
	}

	/**
	 * All the parts represented as rows
	 * @return array
	 */
	abstract protected function rows(): array;

}