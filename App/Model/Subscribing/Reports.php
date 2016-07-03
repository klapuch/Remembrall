<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Reports {
	/**
	 * Go through all the messages
	 * @return Report[]
	 */
	public function iterate(): array;

	/**
	 * Archive the report
	 * @param Part $part
	 * @return void
	 */
	public function archive(Part $part);
}