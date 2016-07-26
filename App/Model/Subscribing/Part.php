<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

interface Part {
	/**
	 * Content of the part
	 * @throws Exception\NotFoundException
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
	 * Refreshed part
	 * @return Part
	 */
	public function refresh(): self;
}