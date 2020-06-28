<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin('Meta', function (RouteBuilder $routes) {
	$routes->fallbacks(DashedRoute::class);
});
