<?php

return array(
    'name' => 'Погода',
    'description' => 'Плагин выводит прогноз погоды',
    'vendor' => '985310',
    'version' => '1.0.3',
    'img' => 'img/weather.png',
    'shop_settings' => true,
    'frontend' => true,
    'icons' => array(
        16 => 'img/weather.png',
    ),
    'handlers' => array(
        'frontend_nav' => 'frontendNav',
    ),
);
