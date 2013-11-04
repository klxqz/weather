<?php
$plugin_id = array('shop', 'weather');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'default_output', '1');
$app_settings_model->set($plugin_id, 'city', '27612');//москва
$app_settings_model->set($plugin_id, 'details', '1');
$app_settings_model->set($plugin_id, 'num_days', '1');
$app_settings_model->set($plugin_id, 'title', 'Погода');