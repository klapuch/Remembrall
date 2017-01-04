<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Log, Uri
};
use Remembrall\Model\Misc;

/**
 * Log every error action
 */
final class LoggedPages extends Misc\LoggingObject implements Pages {
	public function add(Uri\Uri $uri, Page $page): Page {
		return $this->observe(__FUNCTION__, $uri, $page);
	}
}