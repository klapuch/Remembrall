<?php
namespace Remembrall\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Remembrall\Model\Subscribing;

class RouterFactory {
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList;
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Default:default');
		return $router;
	}
}
