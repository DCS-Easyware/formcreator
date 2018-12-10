<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_2_6_5_dcs_2_1($migration) {
   global $DB;

   $configs = Config::getConfigurationValues('formcreator');

   if (!isset($configs['myrequest_sort'])) {
      Config::setConfigurationValues('formcreator', ['myrequest_sort' => 2]);
   }
   if (!isset($configs['myrequest_sortorder'])) {
      Config::setConfigurationValues('formcreator', ['myrequest_sortorder' => 'DESC']);
   }

   if (!isset($configs['allrequest_sort'])) {
      Config::setConfigurationValues('formcreator', ['allrequest_sort' => 2]);
   }
   if (!isset($configs['allrequest_sortorder'])) {
      Config::setConfigurationValues('formcreator', ['allrequest_sortorder' => 'DESC']);
   }

}