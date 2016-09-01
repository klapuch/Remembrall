<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\DuplicateException;
use Klapuch\Uri;

interface Subscriptions {
	/**
	 * Go through all the subscriptions
	 * @return Subscription[]
	 */
	public function iterate(): array;

	/**
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @param Interval $interval
	 * @throws DuplicateException
	 */
	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Interval $interval
	);
}