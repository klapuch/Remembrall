<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Time, Uri
};

interface Subscriptions {
	/**
	 * Subscribe to a new part
	 * @param \Klapuch\Uri\Uri $uri
	 * @param string $expression
	 * @param \Klapuch\Time\Interval $interval
	 * @throws \Remembrall\Exception\DuplicateException
	 * @return void
	 */
	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
	): void;

	/**
	 * Go through all the subscriptions
	 * @return \Iterator
	 */
	public function iterate(): \Iterator;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return Output\Format[]
	 */
	public function print(Output\Format $format): array;
}