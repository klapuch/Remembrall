<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Uri;
use Klapuch\Time;

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
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Traversable
	 */
	public function iterate(Dataset\Selection $selection): \Traversable;
}