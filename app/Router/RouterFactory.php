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
		$router = new RouteList();
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('<year [0-9]{4}>/zavodnik/<id>', 'Competitor:default');
		$router[] = new Route('<year [0-9]{4}>/zavody/add', 'Zavody:add');
		$router[] = new Route('<year [0-9]{4}>/zavody/<id>', 'Zavody:detail');
		$router[] = new Route('<year [0-9]{4}>/<type u23|u18|u14|zeny|u10|u15|u20|u25>[/<show>]', 'Homepage:default');
		$router[] = new Route('<year [0-9]{4}>/soupisky/', 'Rosters:default');
		$router[] = new Route('<year [0-9]{4}>/soupisky/<liga>', 'Rosters:detail');
		$router[] = new Route('<year [0-9]{4}>/tymy/', 'Teams:default');
		$router[] = new Route('<year [0-9]{4}>/tymy/<id>', 'Teams:detail');
		$router[] = new Route('<year [0-9]{4}>/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = new Route('<year [0-9]{4}>/excel-export', 'Homepage:excelExport');
		$router[] = new Route('<presenter>/<action>', 'Homepage:default');
		return $router;
	}

}
