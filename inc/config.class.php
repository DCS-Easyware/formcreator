<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    David Durieux
 * @copyright
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorConfig extends Config {


   function showForm() {
      global $DB;

      if (!Config::canUpdate()) {
         return false;
      }

      $configs = Config::getConfigurationValues('formcreator');

      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo Html::hidden('config_context', ['value' => 'formcreator']);
      echo Html::hidden('config_class', ['value' => 'PluginFormcreatorConfig']);

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('My requests for assistance', 'formcreator') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display search engine', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_myrequest_searchengine", $configs['is_myrequest_searchengine']);
      echo "</td>";
      echo "<td>". __('Display the map', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_myrequest_map", $configs['is_myrequest_map']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display ticket detail', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_myrequest_issuedetail", $configs['is_myrequest_issuedetail']);
      echo "</td>";
      echo "<td>";
      // echo __('Type of tickets to display');
      echo "</td>";
      echo "<td>";
      $elements_types = [
         'ticket' => __('All tickets', 'formcreator'),
         'form'   => __('Only tickets created with forms', 'formcreator')
      ];
      // Dropdown::showFromArray('myrequest_type', $elements_types, ['value' => $configs['myrequest_type']]);
      echo "</td>";
      echo "</tr>";

      $ticket = new Ticket();
      $searches = $ticket->rawSearchOptions();
      $elements = [];
      $prefix = '';
      foreach ($searches as $search) {
         if (is_numeric($search['id'])) {
            $elements[$search['id']] = $prefix.$search['name'];
         } else {
            $prefix = $search['name'].' > ';
         }
      }
      $elements_sortorder = [
         'ASC'  => __('ascending', 'formcreator'),
         'DESC' => __('descending', 'formcreator')
      ];

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Default sort', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('myrequest_sort', $elements, ['value' => $configs['myrequest_sort']]);
      echo "</td>";
      echo "<td>". __('Default order', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray("myrequest_sortorder", $elements_sortorder, ['value' => $configs['myrequest_sortorder']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Fields to search', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('myrequest_searchfields', $elements, ['values'   => importArrayFromDB($configs['myrequest_searchfields']),
                                                                    'multiple' => true]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Columns to display per default', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('myrequest_columns', $elements, ['values'   => importArrayFromDB($configs['myrequest_columns']),
                                                               'multiple' => true]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>" . __('All requests for assistance', 'formcreator') . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display this menu', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_allrequest_enabled", $configs['is_allrequest_enabled']);
      echo "</td>";
      echo "<td>". __('Display search engine', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_allrequest_searchengine", $configs['is_allrequest_searchengine']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display the map', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_allrequest_map", $configs['is_allrequest_map']);
      echo "</td>";
      echo "<td>". __('Display ticket detail', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_allrequest_issuedetail", $configs['is_allrequest_issuedetail']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      // echo __('Type of tickets to display');
      echo "</td>";
      echo "<td>";
      // Dropdown::showFromArray('allrequest_type', $elements_types, ['value' => $configs['allrequest_type']]);
      echo "<td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Default sort', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('allrequest_sort', $elements, ['value' => $configs['allrequest_sort']]);
      echo "</td>";
      echo "<td>". __('Default order', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray("allrequest_sortorder", $elements_sortorder, ['value' => $configs['allrequest_sortorder']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Fields to search', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('allrequest_searchfields', $elements, ['values'   => importArrayFromDB($configs['allrequest_searchfields']),
                                                                    'multiple' => true]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Columns to display per default', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('allrequest_columns', $elements, ['values'   => importArrayFromDB($configs['allrequest_columns']),
                                                               'multiple' => true]);
      echo "</td>";
      echo "</tr>";

      // Display formpart for each group
      $groups_yetadded = importArrayFromDB($configs['group_list']);
      foreach ($groups_yetadded as $groups_id) {
         $this->showFormGroup($groups_id);
      }

      echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();

      // Form to add a new group
      echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post'>";
      echo Html::hidden('config_context', ['value' => 'formcreator']);
      echo Html::hidden('config_class', ['value' => 'PluginFormcreatorConfig']);

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('Add a new group in the menu', 'formcreator') . "</th></tr>";

      // get groups with is_requester
      $elements = [];
      $iterator = $DB->request([
         'FIELDS' => ['id', 'completename'],
         'FROM'   => Group::getTable(),
         'WHERE'  => [
            'is_requester' => 1
         ]
      ]);
      while ($data = $iterator->next()) {
         if (!in_array($data['id'], $groups_yetadded)) {
            $elements[$data['id']] = $data['completename'];
         }
      }

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='2'>". __('Group')."</td>";
      echo "<td colspan='2'>";
      Dropdown::showFromArray('groups_id', $elements);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
      echo "<input type='submit' name='add_group' value=\""._sx('button', 'Add')."\" class='submit'>";
      echo "</td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }


   function showFormGroup($groups_id) {

      $group = new Group();

      $configs = Config::getConfigurationValues('formcreator');
      $group->getFromDB($groups_id);

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('Group') . ": ". $group->fields['completename'] ."</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display this menu', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_grouprequest_".$groups_id."_enabled", $configs['is_grouprequest_'.$groups_id.'_enabled']);
      echo "</td>";
      echo "<td>". __('Display search engine', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_grouprequest_".$groups_id."_searchengine", $configs['is_grouprequest_'.$groups_id.'_searchengine']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Display the map', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_grouprequest_".$groups_id."_map", $configs['is_grouprequest_'.$groups_id.'_map']);
      echo "</td>";
      echo "<td>". __('Display ticket detail', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showYesNo("is_grouprequest_".$groups_id."_issuedetail", $configs['is_grouprequest_'.$groups_id.'_issuedetail']);
      echo "</td>";
      echo "<td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      // echo __('Type of tickets to display');
      echo "</td>";
      echo "<td>";
      $elements_types = [
         'ticket' => __('All tickets', 'formcreator'),
         'form'   => __('Only tickets created with forms', 'formcreator')
      ];
      // Dropdown::showFromArray('grouprequest_'.$groups_id.'_type', $elements_types, ['value' => $configs['grouprequest_'.$groups_id.'_type']]);
      echo "<td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      $ticket = new Ticket();
      $searches = $ticket->rawSearchOptions();
      $elements = [];
      $prefix = '';
      foreach ($searches as $search) {
         if (is_numeric($search['id'])) {
            $elements[$search['id']] = $prefix.$search['name'];
         } else {
            $prefix = $search['name'].' > ';
         }
      }
      $elements_sortorder = [
         'ASC'  => __('ascending', 'formcreator'),
         'DESC' => __('descending', 'formcreator')
      ];

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Default sort', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray('grouprequest_'.$groups_id.'_sort', $elements, ['value' => $configs['grouprequest_'.$groups_id.'_sort']]);
      echo "</td>";
      echo "<td>". __('Default order', 'formcreator')."</td>";
      echo "<td>";
      Dropdown::showFromArray("grouprequest_'.$groups_id.'_sortorder", $elements_sortorder, ['value' => $configs['grouprequest_'.$groups_id.'_sortorder']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Fields to search', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('grouprequest_'.$groups_id.'_searchfields', $elements, ['values'   => importArrayFromDB($configs['grouprequest_'.$groups_id.'_searchfields']),
                                                                    'multiple' => true]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>". __('Columns to display per default', 'formcreator')."</td>";
      echo "<td colspan='3'>";
      Dropdown::showFromArray('grouprequest_'.$groups_id.'_columns', $elements, ['values'   => importArrayFromDB($configs['grouprequest_'.$groups_id.'_columns']),
                                                               'multiple' => true]);
      echo "</td>";
      echo "</tr>";
   }

   /**
    * Prepare the data to update the config, mainly used for arrays
    *
    * @param type $input
    * @return type
    */
   static function configUpdate($input) {
      foreach ($input as $key => $value) {
         if (is_array($value)) {
            $input[$key] = exportArrayToDB($value);
         }
      }
      if (isset($input['_no_history'])) {
         unset($input['_no_history']);
      }
      return $input;
   }


   function addGroup($input) {
      if ($input['groups_id'] > 0) {
         $configs = Config::getConfigurationValues('formcreator');
         $groups = importArrayFromDB($configs['group_list']);
         $groups[] = $input['groups_id'];

         $input_config = [
            'id'                                                    => 1,
            'config_context'                                        => $input['config_context'],
            'config_class'                                          => $input['config_class'],
            'group_list'                                            => exportArrayToDB($groups),
            'is_grouprequest_'.$input['groups_id'].'_enabled'       => 0,
            'is_grouprequest_'.$input['groups_id'].'_searchengine'  => $configs['is_myrequest_searchengine'],
            'is_grouprequest_'.$input['groups_id'].'_map'           => $configs['is_myrequest_map'],
            'is_grouprequest_'.$input['groups_id'].'_issuedetail'   => true,
            'grouprequest_'.$input['groups_id'].'_type'             => 'form',
            'grouprequest_'.$input['groups_id'].'_sort'             => $configs['myrequest_sort'],
            'grouprequest_'.$input['groups_id'].'_sortorder'        => $configs['myrequest_sortorder'],
            'grouprequest_'.$input['groups_id'].'_searchfields'     => importArrayFromDB($configs['myrequest_searchfields']),
            'grouprequest_'.$input['groups_id'].'_columns'          => importArrayFromDB($configs['myrequest_columns']),
         ];
         $config = new Config();
         $config->update($input_config);
      }
   }
}
