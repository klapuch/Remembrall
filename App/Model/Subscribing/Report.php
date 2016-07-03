<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Access;

interface Report {
	/**
	 * The one for whom is the report generated
	 * @return Access\Subscriber
	 */
	public function recipient(): Access\Subscriber;

	/**
	 * Content is represented as a part
	 * @return Part
	 */
	public function content(): Part;

	/**
	 * DateTime when was the report generated at
	 * @return \DateTimeImmutable
	 */
	public function sentAt(): \DateTimeImmutable;
}