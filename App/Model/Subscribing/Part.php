<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

interface Part {
	/**
	 * Content of the part
	 * @throws \Remembrall\Exception\NotFoundException
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
	 * @return \Remembrall\Model\Subscribing\Part
	 */
	public function refresh(): self;

	/**
	 * Print the part
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}