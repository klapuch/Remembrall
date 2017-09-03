<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Time;
use Klapuch\Uri;

interface Subscriptions {
	/**
	 * Subscribe to a new part
	 * @param \Klapuch\Uri\Uri $uri
	 * @param string $expression
	 * @param string $language
	 * @param \Klapuch\Time\Interval $interval
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		string $language,
		Time\Interval $interval
	): void;

	/**
	 * Go through all the subscriptions
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Traversable
	 */
	public function all(Dataset\Selection $selection): \Traversable;
}