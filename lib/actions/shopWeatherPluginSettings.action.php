<?php

class shopWeatherPluginSettingsAction extends waViewAction
{

    public function execute()
    {
        $weather = wa()->getPlugin('weather');
        $cities = $weather->getCities();
        $settings = $weather->getSettings();

        $this->view->assign('cities', $cities);
        $this->view->assign('settings', $settings);
        

    }
}
