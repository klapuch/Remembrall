<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

/**
 * Parts name for Redis identifier
 */
final class PartsName {
	public function __toString(): string {
		return 'parts';
	}
}