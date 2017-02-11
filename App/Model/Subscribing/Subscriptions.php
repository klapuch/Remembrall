<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Time, Uri
};

interface Subscriptions extends \IteratorAggregate {
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
}