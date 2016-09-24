<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\DuplicateException;
use Klapuch\{
    Uri, Time, Output
};

interface Subscriptions {
    /**
     * Subscribe to a new part
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @param Time\Interval $interval
	 * @throws DuplicateException
	 * @return void
	 */
	public function subscribe(
		Uri\Uri $uri,
		string $expression,
		Time\Interval $interval
    );

	/**
	 * Print itself
	 * @param Output\Format $format
	 * @return Output\Format[]
	 */
	public function print(Output\Format $format): array;

}
