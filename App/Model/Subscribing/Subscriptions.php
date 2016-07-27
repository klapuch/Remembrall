<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Subscriptions {
	/**
	 * Go through all the subscriptions
	 * @return Subscription[]
	 */
	public function iterate(): array;

	/**
	 * @param string $url
	 * @param string $expression
	 * @param Interval $interval
	 * @throws Exception\DuplicateException
	 */
	public function subscribe(
		string $url,
		string $expression,
		Interval $interval
	);
}