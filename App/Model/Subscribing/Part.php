<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\Access;

interface Part {
	/**
	 * Source where the part comes from
	 * @return Page
	 */
	public function source(): Page;

	/**
	 * Content of the part
	 * @throws Exception\ExistenceException
	 * @return string
	 */
	public function content(): string;

	/**
	 * Is the given part equals to the current one?
	 * @param Part $part
	 * @return bool
	 */
	public function equals(self $part): bool;

	/**
	 * Every part can be identified by expression
	 * @return Expression
	 */
	public function expression(): Expression;

	/**
	 * When was the part visited at? What is the next planning visitation?
	 * @return Interval
	 */
	public function visitedAt(): Interval;
}