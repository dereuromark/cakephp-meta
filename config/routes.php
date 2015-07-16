<?php
use Cake\Routing\Router;

Router::plugin('Meta', function ($routes) {
    $routes->fallbacks('DashedRoute');
});
