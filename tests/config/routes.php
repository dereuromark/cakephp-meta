<?php

use Cake\Core\Plugin;
use Cake\Routing\Router;

Router::plugin('Meta', function ($routes) {
	$routes->fallbacks('DashedRoute');
});

Plugin::routes();
