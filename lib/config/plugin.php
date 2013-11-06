<?php

return array(
    'name' => 'Погода',
    'description' => 'Плагин выводит прогноз погоды',
    'vendor' => 903438,
    'version' => '1.0.1',
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
