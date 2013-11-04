<?php

class shopWeatherPluginBackendSaveController extends waJsonController {
    
    public function execute()
    {
        try {
            $shop_weather = waRequest::post('shop_weather');
            $app_settings_model = new waAppSettingsModel();

            foreach($shop_weather as $name => $value) {
                $app_settings_model->set(array('shop','weather'),$name,$value);
            }
            
            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}