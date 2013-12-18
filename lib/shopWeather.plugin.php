<?php

class shopWeatherPlugin extends shopPlugin {

    protected static $plugin;

    public function __construct($info) {
        parent::__construct($info);
        if (!self::$plugin) {
            self::$plugin = &$this;
        }
    }

    protected static function getThisPlugin() {
        if (self::$plugin) {
            return self::$plugin;
        } else {
            return wa()->getPlugin('weather');
        }
    }

    public function frontendNav() {
        if ($this->getSettings('default_output')) {
            return self::display();
        }
    }

    public function dateFormat($date) {
        $days = array('Mon' => 'Понедельник', 'Tue' => 'Вторник', 'Wed' => 'Среда', 'Thu' => 'Четверг', 'Fri' => 'Пятница', 'Sat' => 'Суббота', 'Sun' => 'Воскресенье');
        $months = array(1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля', 5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа', 9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря');

        $time = strtotime($date);
        $day = date('D', $time);
        $week_day = $days[$day];

        $m = date('m', $time);
        $month = $months[$m];

        return array('week_day' => $week_day, 'month' => $month, 'd' => date('d', $time), 'm' => date('m', $time), 'Y' => date('Y', $time));
    }

    public static function display() {
        $plugin = self::getThisPlugin();

        if ($plugin->getSettings('status')) {
            $city_id = $plugin->getSettings('city');
            $weather_data = $plugin->getWeather($city_id);
            $settings = $plugin->getSettings();

            foreach ($weather_data as $day => &$data) {
                $data['date'] = $plugin->dateFormat($day);
            }

            if ($settings['num_days']) {
                $weather_data = array_slice($weather_data, 0, $settings['num_days']);
            }

            $view = wa()->getView();
            $view->assign('weather_data', $weather_data);
            $view->assign('settings', $settings);
            $template_path = wa()->getAppPath('plugins/weather/templates/Weather.html', 'shop');
            $html = $view->fetch($template_path);
            return $html;
        }
    }

    public function getCities() {
        $result = array();

        $cache = new waSerializeCache($this->app_id . $this->id . '.' . 'cities');

        if ($cache && $cache->isCached()) {
            $result = $cache->get();
        } else {
            $url = 'http://weather.yandex.ru/static/cities.xml';
            $xml = $this->sendRequest($url, null, 'GET');
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->encoding = 'UTF-8';
            $dom->loadXML($xml);
            $countris = $dom->getElementsByTagName('country');

            foreach ($countris as $country) {
                $cities = $country->getElementsByTagName('city');
                $country_name = $country->getAttribute('name');
                foreach ($cities as $city) {
                    $result[$country_name][] = array('name' => $city->nodeValue, 'id' => $city->getAttribute('id'));
                }
            }


            if ($result && $cache) {
                $cache->set($result);
            }
        }
        return $result;
    }

    public function getWeather($city_id) {
        $result = array();

        $current_datetime = waDateTime::date("Y-m-d-H", null, wa()->getUser()->getTimezone());
        $cache = new waSerializeCache($this->app_id . $this->id . '.' . 'weather.' . $city_id . '.' . $current_datetime);

        if ($cache && $cache->isCached()) {
            $result = $cache->get();
        } else {

            $f_url = 'http://export.yandex.ru/weather-ng/forecasts/%d.xml';
            $url = sprintf($f_url, $city_id);
            $xml = $this->sendRequest($url, null, 'GET');
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->encoding = 'UTF-8';
            $dom->loadXML($xml);
            $days = $dom->getElementsByTagName('day');
            foreach ($days as $day) {
                $date = $day->getAttribute('date');

                $day_data = array(
                    'sunrise' => @$day->getElementsByTagName('sunrise')->item(0)->nodeValue,
                    'sunset' => @$day->getElementsByTagName('sunset')->item(0)->nodeValue,
                    'sunset' => @$day->getElementsByTagName('sunset')->item(0)->nodeValue,
                    'moonrise' => @$day->getElementsByTagName('moonrise')->item(0)->nodeValue,
                    'moonset' => @$day->getElementsByTagName('moonset')->item(0)->nodeValue,
                );
                $day_parts = $day->getElementsByTagName('day_part');
                foreach ($day_parts as $day_part) {
                    $type = $day_part->getAttribute('type');
                    $day_part_data = array(
                        'temperature_from' => @$day_part->getElementsByTagName('temperature_from')->item(0)->nodeValue,
                        'temperature_to' => @$day_part->getElementsByTagName('temperature_to')->item(0)->nodeValue,
                        'temperature' => @$day_part->getElementsByTagName('temperature')->item(0)->nodeValue,
                        'image' => @$day_part->getElementsByTagName('image')->item(0)->nodeValue,
                        'image-v2' => @$day_part->getElementsByTagName('image-v2')->item(0)->nodeValue,
                        'image-v3' => @$day_part->getElementsByTagName('image-v3')->item(0)->nodeValue,
                        'weather_type' => @$day_part->getElementsByTagName('weather_type')->item(0)->nodeValue,
                        'weather_type_short' => @$day_part->getElementsByTagName('weather_type_short')->item(0)->nodeValue,
                    );
                    $day_data[$type] = $day_part_data;
                }

                $result[$date] = $day_data;
            }

            if ($result && $cache) {
                $cache->set($result);
            }
        }
        return $result;
    }

    protected function sendRequest($url, $data = null, $method = 'POST') {
        if (!extension_loaded('curl') || !function_exists('curl_init')) {
            throw new waException('PHP расширение cURL не доступно');
        }

        if (!($ch = curl_init())) {
            throw new waException('curl init error');
        }

        if (curl_errno($ch) != 0) {
            throw new waException('Ошибка инициализации curl: ' . curl_errno($ch));
        }

        $data = json_encode($data);
        $headers = array("Content-Type: application/json");

        @curl_setopt($ch, CURLOPT_URL, $url);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            @curl_setopt($ch, CURLOPT_POST, 1);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $response = @curl_exec($ch);
        $app_error = null;
        if (curl_errno($ch) != 0) {
            $app_error = 'Ошибка curl: ' . curl_error($ch);
        }
        curl_close($ch);
        if ($app_error) {
            throw new waException($app_error);
        }
        if (empty($response)) {
            throw new waException('Пустой ответ от сервера');
        }

        $json = json_decode($response, true);

        $return = json_decode($response, true);
        if (!is_array($return)) {
            return $response;
        } else {
            return $return;
        }
    }

}
