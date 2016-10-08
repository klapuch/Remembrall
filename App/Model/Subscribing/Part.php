<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

interface Part {
	/**
	 * Content of the part
	 * @throws NotFoundException
	 * @return string
	 */
	public function content(): string;

	/**
	 * Snapshot of the part
	 * @return string
	 */
	public function snapshot(): string;

	/**
	 * Refreshed part
	 * @return Part
	 */
	public function refresh(): self;
}