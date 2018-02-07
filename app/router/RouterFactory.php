<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;


final class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();
		// Setup router
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('<rok [0-9]{4}>/zavodnik/<id>', 'Zavodnik:default');
		$router[] = new Route('<rok [0-9]{4}>/zavody/add', 'Zavody:add');
		$router[] = new Route('<rok [0-9]{4}>/zavody/<id>', 'Zavody:detail');
		$router[] = new Route('<rok [0-9]{4}>/<typ u23|u18|u14|zeny|u10|u15|u20|u25>[/<show>]', ['presenter' => 'Homepage', 'action' => 'default']);
		$router[] = new Route('<rok [0-9]{4}>/soupisky/<liga>', ['presenter' => 'Soupisky', 'action' => 'detail']);
		$router[] = new Route('<rok [0-9]{4}>/<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = new Route('<rok [0-9]{4}>/excel-export', 'Homepage:excelExport');


		$router[] = new Route('zavodnik/<id>', 'Zavodnik:default');
		$router[] = new Route('zavody/add', 'Zavody:add');
		$router[] = new Route('zavody/<id>', 'Zavody:detail');

		$router[] = new Route('<typ u23|u18|u14|zeny|u10|u15|u20|u25>[/<show>]', ['presenter' => 'Homepage', 'action' => 'default']);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
