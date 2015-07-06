<?php
use Cake\Routing\Router;
use Cake\Core\Plugin;

Router::plugin('Meta', function ($routes) {
	$routes->fallbacks('DashedRoute');
});

Plugin::routes();
