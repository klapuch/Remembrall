<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\DuplicateException;

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
	 * @throws DuplicateException
	 */
	public function subscribe(
		string $url,
		string $expression,
		Interval $interval
	);
}