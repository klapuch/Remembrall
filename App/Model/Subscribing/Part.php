<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

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
}