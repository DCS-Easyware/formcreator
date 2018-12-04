<?php
/**
 *
 * @param Migration $migration
 *
 * @return void
 */
function plugin_formcreator_update_2_6_5_dcs_1_0($migration) {
   global $DB;

   // Upgrade plugin configuration table
   $table = 'glpi_plugin_formcreator_entityconfigs';
   $migration->displayMessage("Upgrade $table");
   $migration->addField($table, 'external_links_prefix', 'string', ['after' => 'replace_helpdesk']);
   $migration->addField($table, 'external_links_icon', 'string', ['after' => 'external_links_prefix']);
   $migration->addField($table, 'external_links_title', 'string', ['after' => 'external_links_icon']);
   $migration->addField($table, 'tickets_summary', 'integer', ['after' => 'external_links_title', 'value' => '1']);
   $migration->addField($table, 'user_preferences', 'integer', ['after' => 'tickets_summary', 'value' => '1']);
   $migration->addField($table, 'avatar', 'integer', ['after' => 'user_preferences', 'value' => '1']);
   $migration->addField($table, 'user_name', 'integer', ['after' => 'avatar', 'value' => '0']);
   $migration->addField($table, 'profile_selector', 'integer', ['after' => 'user_name', 'value' => '1']);
   $migration->addField($table, 'use_favorites', 'integer', ['after' => 'profile_selector', 'value' => '0']);
   $migration->addField($table, 'use_search_engine', 'integer', ['after' => 'use_favorites', 'value' => '1']);
   $migration->addField($table, 'header_title', 'string', ['after' => 'use_search_engine', 'value' => '']);
   $migration->addField($table, 'page_title', 'string', ['after' => 'header_title', 'value' => '']);
   $migration->addField($table, 'extra_css_uri', 'string', ['after' => 'page_title', 'value' => '']);
}
