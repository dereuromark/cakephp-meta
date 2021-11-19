<?php
/**
 * @var \Cake\Routing\RouteBuilder $routes
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

$routes->plugin('Meta', function (RouteBuilder $routes) {
	$routes->fallbacks(DashedRoute::class);
});
