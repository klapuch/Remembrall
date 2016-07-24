<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface RemindedPassword {
	/**
	 * Change the reminded password to the new given one
	 * @param string $password
	 * @return void
	 */
	public function change(string $password);
}