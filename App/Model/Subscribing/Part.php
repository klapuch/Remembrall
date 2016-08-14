<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Remembrall\Exception\NotFoundException;

interface Part {
	/**
	 * Content of the part
	 * @throws NotFoundException
	 * @return string
	 */
	public function content(): string;

	/**
	 * Refreshed part
	 * @return Part
	 */
	public function refresh(): self;

	/**
	 * Print itself to the given format
	 * @return Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}