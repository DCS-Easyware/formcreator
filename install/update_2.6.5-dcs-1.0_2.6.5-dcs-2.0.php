<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_2_6_5_dcs_2_0($migration) {
   global $DB;

   $configs = Config::getConfigurationValues('formcreator');
   if (!isset($configs['group_list'])) {
      Config::setConfigurationValues('formcreator', ['group_list' => exportArrayToDB([])]);
   }

   if (!isset($configs['is_myrequest_searchengine'])) {
      Config::setConfigurationValues('formcreator', ['is_myrequest_searchengine' => true]);
   }
   if (!isset($configs['is_myrequest_map'])) {
      Config::setConfigurationValues('formcreator', ['is_myrequest_map' => true]);
   }
   if (!isset($configs['myrequest_type'])) {
      Config::setConfigurationValues('formcreator', ['myrequest_type' => 'form']);
   }
   if (!isset($configs['myrequest_searchfields'])) {
      Config::setConfigurationValues('formcreator', ['myrequest_searchfields' => exportArrayToDB([])]);
   }
   if (!isset($configs['myrequest_columns'])) {
      Config::setConfigurationValues('formcreator', ['myrequest_columns' => exportArrayToDB([])]);
   }

   if (!isset($configs['is_allrequest_enabled'])) {
      Config::setConfigurationValues('formcreator', ['is_allrequest_enabled' => true]);
   }
   if (!isset($configs['allrequest_type'])) {
      Config::setConfigurationValues('formcreator', ['allrequest_type' => 'form']);
   }
   if (!isset($configs['is_allrequest_searchengine'])) {
      Config::setConfigurationValues('formcreator', ['is_allrequest_searchengine' => true]);
   }
   if (!isset($configs['is_allrequest_map'])) {
      Config::setConfigurationValues('formcreator', ['is_allrequest_map' => true]);
   }
   if (!isset($configs['allrequest_searchfields'])) {
      Config::setConfigurationValues('formcreator', ['allrequest_searchfields' => exportArrayToDB([])]);
   }
   if (!isset($configs['allrequest_columns'])) {
      Config::setConfigurationValues('formcreator', ['allrequest_columns' => exportArrayToDB([])]);
   }

}