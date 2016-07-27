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
	 * Refreshed part
	 * @return Part
	 */
	public function refresh(): self;
}