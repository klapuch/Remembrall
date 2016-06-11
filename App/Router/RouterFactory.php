<?php
namespace Remembrall\Router;

use Nette\Application\Routers\{
	RouteList, Route
};

class RouterFactory {
	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList;
		$router[] = new Route(
			'<presenter>[/<action>][/<id [0-9]+>]',
			'Default:default',
			Route::SECURED
		);
		return $router;
	}
}
