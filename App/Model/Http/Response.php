<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

interface Response {
	/**
	 * Content returned by the host
	 * @throws Exception\NotFoundException
	 * @return string
	 */
	public function content(): string;
}