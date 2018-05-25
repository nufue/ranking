<?php
declare(strict_types=1);

namespace App\Router;

use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


final class RouterFactory
{

	public static function createRouter(): IRouter
	{
		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/<year [0-9]{4}>/<presenter>/<action>[/<id>]', 'Homepage:default');
		$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default');

		$frontRouter = new RouteList('Front');
		$frontRouter[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$frontRouter[] = new Route('<year [0-9]{4}>/zavodnik/<id>', 'Competitor:default');
		$frontRouter[] = new Route('<year [0-9]{4}>/zavody/add', 'Zavody:add');
		$frontRouter[] = new Route('<year [0-9]{4}>/zavody/<id>', 'Zavody:detail');
		$frontRouter[] = new Route('<year [0-9]{4}>/<type u23|u18|u14|zeny|u10|u15|u20|u25>[/<show>]', 'Homepage:default');
		$frontRouter[] = new Route('<year [0-9]{4}>/soupisky/', 'Rosters:default');
		$frontRouter[] = new Route('<year [0-9]{4}>/soupisky/<liga>', 'Rosters:detail');
		$frontRouter[] = new Route('<year [0-9]{4}>/tymy/', 'Teams:default');
		$frontRouter[] = new Route('<year [0-9]{4}>/tymy/<id>', 'Teams:detail');
		$frontRouter[] = new Route('<year [0-9]{4}>/<presenter>/<action>[/<id>]', 'Homepage:default');
		$frontRouter[] = new Route('<year [0-9]{4}>/excel-export', 'Homepage:excelExport');
		$frontRouter[] = new Route('<presenter>/<action>', 'Homepage:default');

		$routes = new RouteList();
		$routes[] = $adminRouter;
		$routes[] = $frontRouter;
		return $routes;
	}

}
