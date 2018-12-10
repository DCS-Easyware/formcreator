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
 * @author    Thierry Bugier
 * @author    Jérémy Moreau
 * @copyright Copyright © 2011 - 2018 Teclib'
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFormcreatorIssue extends CommonDBTM {
   static $rightname = 'ticket';

   public static function getTypeName($nb = 0) {
      return _n('Issue', 'Issues', $nb, 'formcreator');
   }

   /**
    * get Cron description parameter for this class
    *
    * @param $name string name of the task
    *
    * @return array of string
    */
   static function cronInfo($name) {
      switch ($name) {
         case 'SyncIssues':
            return ['description' => __('Update issue data from tickets and form answers', 'formcreator')];
      }
   }

   /**
    *
    * @param CronTask $task
    *
    * @return number
    */
   public static function cronSyncIssues(CronTask $task) {
      global $DB;

      $task->log("Sync issues from forms answers and tickets");
      $volume = 0;

      // Request which merges tickets and formanswers
      // 1 ticket not linked to a form_answer => 1 issue which is the ticket sub_itemtype
      // 1 form_answer not linked to a ticket => 1 issue which is the form_answer sub_itemtype
      // 1 ticket linked to 1 form_answer => 1 issue which is the ticket sub_itemtype
      // several tickets linked to the same form_answer => 1 issue which is the form_answer sub_itemtype
      $query = "SELECT DISTINCT
                  NULL                           AS `id`,
                  CONCAT('f_',`fanswer`.`id`)    AS `display_id`,
                  `fanswer`.`id`                 AS `original_id`,
                  'PluginFormcreatorForm_Answer' AS `sub_itemtype`,
                  `f`.`name`                     AS `name`,
                  `fanswer`.`status`             AS `status`,
                  `fanswer`.`request_date`       AS `date_creation`,
                  `fanswer`.`request_date`       AS `date_mod`,
                  `fanswer`.`entities_id`        AS `entities_id`,
                  `fanswer`.`is_recursive`       AS `is_recursive`,
                  `fanswer`.`requester_id`       AS `requester_id`,
                  `fanswer`.`users_id_validator` AS `validator_id`,
                  `fanswer`.`comment`            AS `comment`
               FROM `glpi_plugin_formcreator_forms_answers` AS `fanswer`
               LEFT JOIN `glpi_plugin_formcreator_forms` AS `f`
                  ON`f`.`id` = `fanswer`.`plugin_formcreator_forms_id`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`items_id` = `fanswer`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
               WHERE `fanswer`.`is_deleted` = '0'
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`tickets_id`) != 1

               UNION

               SELECT DISTINCT
                  NULL                          AS `id`,
                  CONCAT('t_',`tic`.`id`)       AS `display_id`,
                  `tic`.`id`                    AS `original_id`,
                  'Ticket'                      AS `sub_itemtype`,
                  `tic`.`name`                  AS `name`,
                  `tic`.`status`                AS `status`,
                  `tic`.`date`                  AS `date_creation`,
                  `tic`.`date_mod`              AS `date_mod`,
                  `tic`.`entities_id`           AS `entities_id`,
                  0                             AS `is_recursive`,
                  `tic`.`users_id_recipient`    AS `requester_id`,
                  0                             AS `validator_id`,
                  `tic`.`content`               AS `comment`
               FROM `glpi_tickets` AS `tic`
               LEFT JOIN `glpi_items_tickets` AS `itic`
                  ON `itic`.`tickets_id` = `tic`.`id`
                  AND `itic`.`itemtype` = 'PluginFormcreatorForm_Answer'
               WHERE `tic`.`is_deleted` = 0
               GROUP BY `original_id`
               HAVING COUNT(`itic`.`items_id`) <= 1";

      $countQuery = "SELECT COUNT(*) AS `cpt` FROM ($query) AS `issues`";
      $result = $DB->query($countQuery);
      if ($result !== false) {
         $count = $DB->fetch_assoc($result);
         $table = static::getTable();
         if (countElementsInTable($table) != $count['cpt']) {
            if ($DB->query("TRUNCATE `$table`")) {
               $DB->query("INSERT INTO `$table` SELECT * FROM ($query) as `dt`");
               $volume = 1;
            }
         }
      }
      $task->setVolume($volume);

      return 1;
   }

   public static function hook_update_ticket(CommonDBTM $item) {

   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function display($options = []) {
      global $CFG_GLPI;

      $itemtype = $options['sub_itemtype'];
      if (!in_array($itemtype, ['Ticket', 'PluginFormcreatorForm_Answer'])) {
         html::displayRightError();
      }
      if ($CFG_GLPI['use_rich_text']) {
         Html::requireJs('tinymce');
      }
      if (plugin_formcreator_replaceHelpdesk() == PluginFormcreatorEntityconfig::CONFIG_SIMPLIFIED_SERVICE_CATALOG) {
         $this->displaySimplified($options);
      } else {
         $this->displayExtended($options);
      }
   }

   public function displayExtended($options = []) {
      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
            && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // if ticket(s) exist(s), show it/them
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorForm_Answer) {
         $item = $this->getTicketsForDisplay($options);
      }

      $item->showTabsContent();

   }

   /**
    * {@inheritDoc}
    * @see CommonGLPI::display()
    */
   public function displaySimplified($options = []) {
      global $CFG_GLPI;

      $item = new $options['sub_itemtype'];

      if (isset($options['id'])
          && !$item->isNewID($options['id'])) {
         if (!$item->getFromDB($options['id'])) {
            Html::displayNotFoundError();
         }
      }

      // in case of left tab layout, we couldn't see "right error" message
      if ($item->get_item_to_display_tab) {
         if (isset($options["id"])
             && $options["id"]
             && !$item->can($options["id"], READ)) {
            // This triggers from a profile switch.
            // If we don't have right, redirect instead to central page
            if (isset($_SESSION['_redirected_from_profile_selector'])
                && $_SESSION['_redirected_from_profile_selector']) {
               unset($_SESSION['_redirected_from_profile_selector']);
               Html::redirect($CFG_GLPI['root_doc']."/front/central.php");
            }

            html::displayRightError();
         }
      }

      if (!isset($options['id'])) {
         $options['id'] = 0;
      }

      // Header if the item + link to the list of items
      $this->showNavigationHeader($options);

      // retrieve associated tickets
      $options['_item'] = $item;
      if ($item Instanceof PluginFormcreatorForm_Answer) {
         $item = $this->getTicketsForDisplay($options);
      }

      // force recall of ticket in layout
      $old_layout = $_SESSION['glpilayout'];
      $_SESSION['glpilayout'] = "lefttab";

      if ($item instanceof Ticket) {
         //Tickets without form associated or single ticket for an answer
         echo "<div class='timeline_box'>";
         $rand = mt_rand();
         $item->showTimelineForm($rand);
         $item->showTimeline($rand);
         echo "</div>";
      } else {
         // No ticket associated to this issue or multiple tickets
         // Show the form answers
         echo '<div class"center">';
         $item->showTabsContent();
         echo '</div>';
      }

      // restore layout
      $_SESSION['glpilayout'] = $old_layout;
   }

   /**
    * Retrieve how many ticket associated to the current answer
    * @param  array $options must contains at least an _item key, instance for answer
    * @return mixed the provide _item key replaced if needed
    */
   public function getTicketsForDisplay($options) {
      $item = $options['_item'];
      $formanswerId = $options['id'];
      $item_ticket = new Item_Ticket();
      $rows = $item_ticket->find("`itemtype` = 'PluginFormcreatorForm_Answer'
                                  AND `items_id` = $formanswerId", "`tickets_id` ASC");

      if (count($rows) == 1) {
         // one ticket, replace item
         $ticket = array_shift($rows);
         $item = new Ticket;
         $item->getFromDB($ticket['tickets_id']);
      } else if (count($rows) > 1) {
         // multiple tickets, force ticket tab in form anser
         Session::setActiveTab('PluginFormcreatorForm_Answer', 'Ticket$1');
      }

      return $item;
   }

   /**
    * Define search options for forms
    *
    * @return Array Array of fields to show in search engine and options for each fields
    */
   public function getSearchOptionsNew() {
      return $this->rawSearchOptions();
   }

   public function rawSearchOptions() {
      $ticket = new Ticket();
      if (isset($_SESSION['glpi_plugin_formcreator_restrictsearchoptions'])) {
         $tab = $ticket->rawSearchOptions();
         $configs = Config::getConfigurationValues('formcreator');

         if ($_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] == 'myrequest') {
            $searchfields = importArrayFromDB($configs['myrequest_searchfields']);
            $columns = importArrayFromDB($configs['myrequest_columns']);
         } else if ($_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] == 'allrequest') {
            $searchfields = importArrayFromDB($configs['allrequest_searchfields']);
            $columns = importArrayFromDB($configs['allrequest_columns']);
         } else if (strstr($_SESSION['glpi_plugin_formcreator_restrictsearchoptions'], 'group')) {
            $vals = explode('-', $_SESSION['glpi_plugin_formcreator_restrictsearchoptions']);
            $searchfields = importArrayFromDB($configs['grouprequest_'.$vals[1].'_searchfields']);
            $columns = importArrayFromDB($configs['grouprequest_'.$vals[1].'_columns']);
         } else {
            return [];
         }
         $group = [];
         $tablist = [];
         $searchid_required = [2, 83, 12];
         // case for after map search
         if (isset($_GET['criteria'])) {
            foreach ($_GET['criteria'] as $criteria) {
               if (($criteria['field'] == 998
                     || $criteria['field'] == 999)
                    && !in_array($criteria['field'], $searchid_required)) {
                  $searchid_required[] = $criteria['field'];
               }
            }
         }

         foreach ($tab as $row) {
            if (!isset($row['id'])) {
               continue;
            } else if (!is_numeric($row['id'])) {
               if (count($group) > 1) {
                  $tablist = array_merge($tablist, $group);
               }
               $group = [];
               $group[] = $row;
            } else if (in_array($row['id'], $searchfields)
                  || in_array($row['id'], $columns)
                  || in_array($row['id'], $searchid_required)) {
               $group[] = $row;
            }
         }
         if (count($group) > 1) {
            $tablist = array_merge($tablist, $group);
         }
         return $tablist;
      }
      return $ticket->rawSearchOptions();
   }

   /**
    *
    * @param boolean $restricted (if true, get only allowed in the config)
    */
   public function getRawSearchOptions($restricted=true) {
      $tab = [];

   }

   public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'sub_itemtype':
            return Dropdown::showFromArray($name,
                                           ['Ticket'                      => __('Ticket'),
                                            'PluginFormcreatorForm_Answer' => __('Form answer', 'formcreator')],
                                           ['display' => false,
                                            'value'   => $values[$field]]);
         case 'status' :
            $ticket_opts = Ticket::getAllStatusArray(true);
            $ticket_opts['waiting'] = __('Not validated');
            $ticket_opts['refused'] = __('Refused');
            return Dropdown::showFromArray($name, $ticket_opts, ['display' => false,
                                                                 'value'   => $values[$field]]);
            break;

      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   static function getDefaultSearchRequest() {

      $search = ['criteria' => [0 => ['field'      => 12,
                                      'searchtype' => 'equals',
                                      'value'      => 'notclosed']],
                 'sort'     => 2,
                 'order'    => 'DESC'];

      if (Session::haveRight(self::$rightname, Ticket::READALL)) {
         $search['criteria'][0]['value'] = 'notold';
      }
      return $search;
   }

   public static function giveItem($itemtype, $option_id, $data, $num) {
      $searchopt = &Search::getOptions($itemtype);
      $table = $searchopt[$option_id]["table"];
      $field = $searchopt[$option_id]["field"];

      if (isset($data['raw']['ITEM_0_display_id'])) {
         $matches = null;
         preg_match('/[tf]+_([0-9]*)/', $data['raw']['ITEM_0_display_id'], $matches);
         $id = $matches[1];
      }

      switch ("$table.$field") {
         case "glpi_plugin_formcreator_issues.name":
            $name = $data[$num][0]['name'];
            return "<a href='".FORMCREATOR_ROOTDOC."/front/issue.form.php?id=".$id."&sub_itemtype=".$data['raw']['sub_itemtype']."'>$name</a>";
            break;

         case "glpi_plugin_formcreator_issues.id":
            return $data['raw']['id'];
            break;

         case "glpi_plugin_formcreator_issues.status":
            switch ($data['raw']['sub_itemtype']) {
               case 'Ticket':
                  $status = Ticket::getStatus($data['raw']["ITEM_$num"]);
                  if (version_compare(PluginFormcreatorCommon::getGlpiVersion(), '9.3') < 0) {
                     return "<img src='".Ticket::getStatusIconUrl($data['raw']["ITEM_$num"])."'
                                 alt=\"$status\" title=\"$status\">&nbsp;$status";
                  }
                  return Ticket::getStatusIcon($data['raw']["ITEM_$num"]);
                  break;

               case 'PluginFormcreatorForm_Answer':
                  return PluginFormcreatorForm_Answer::getSpecificValueToDisplay('status', $data['raw']["ITEM_$num"]);
                  break;
            }
            break;
      }

      return "";
   }

   static function getClosedStatusArray() {
      return Ticket::getClosedStatusArray();
   }

   static function getSolvedStatusArray() {
      return Ticket::getSolvedStatusArray();
   }

   static function getNewStatusArray() {
      return [Ticket::INCOMING, 'waiting', 'accepted', 'refused'];
   }

   static function getProcessStatusArray() {
      return Ticket::getProcessStatusArray();
   }

   static function getReopenableStatusArray() {
      return Ticket::getReopenableStatusArray();
   }

   static function getAllStatusArray($withmetaforsearch = false) {
      $ticket_status = Ticket::getAllStatusArray($withmetaforsearch);
      $form_status = ['waiting', 'accepted', 'refused'];
      $form_status = array_combine($form_status, $form_status);
      $all_status = $ticket_status + $form_status;
      return $all_status;
   }

   static function getIncomingCriteria() {
      return[
         'criteria' => [
            [
               'field' => 12,
               'searchtype' => 'equals',
               'value'      => 'notold'
            ],
            [
               'link'       => 'AND',
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => $_SESSION['glpiID']
            ]
         ],
         'reset'    => 'reset'
      ];
   }

   static function getWaitingCriteria() {
      return[
         'criteria' => [
            [
               'field' => 12,
               'searchtype' => 'equals',
               'value'      => Ticket::WAITING
            ],
            [
               'link'       => 'AND',
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => $_SESSION['glpiID']
            ]
         ],
         'reset'    => 'reset'
      ];
   }

   static function getValidateCriteria() {
      return ['criteria' => [['field' => 4,
               'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => 'notclosed',
                              'link'       => 'AND'],
                             ['field' => 9,
               'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => $_SESSION['glpiID'],
                              'link'       => 'AND'],
                             ['field' => 4,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => 'notclosed',
                              'link'       => 'OR'],
                             ['field' => 11,
                              'searchtype' => 'equals',
                              'value'      => 'process',
                              'value'      => $_SESSION['glpiID'],
                              'link'       => 'AND']],
              'reset'    => 'reset'];
   }

   static function getSolvedCriteria() {
      return[
         'criteria' => [
            [
               'field' => 12,
               'searchtype' => 'equals',
               'value'      => 'old'
            ],
            [
               'link'       => 'AND',
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => $_SESSION['glpiID']
            ]
         ],
         'reset'    => 'reset'
      ];
   }

   static function getTicketSummary() {
      $status = [
         Ticket::INCOMING => 0,
         Ticket::WAITING => 0,
         'to_validate' => 0,
         Ticket::SOLVED => 0
      ];
      $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = 'myrequest';

      $searchIncoming = Search::getDatas(Ticket::class,
                                         self::getIncomingCriteria());
      if ($searchIncoming['data']['totalcount'] > 0) {
         $status[Ticket::INCOMING] = $searchIncoming['data']['totalcount'];
      }

      $searchWaiting = Search::getDatas(Ticket::class,
                                         self::getWaitingCriteria());
      if ($searchWaiting['data']['totalcount'] > 0) {
         $status[Ticket::WAITING] = $searchWaiting['data']['totalcount'];
      }

      $searchValidate = Search::getDatas(Ticket::class,
                                         self::getValidateCriteria());
      if ($searchValidate['data']['totalcount'] > 0) {
         $status['to_validate'] = $searchValidate['data']['totalcount'];
      }

      $searchSolved = Search::getDatas(Ticket::class,
                                         self::getSolvedCriteria());
      if ($searchSolved['data']['totalcount'] > 0) {
         $status[Ticket::SOLVED] = $searchSolved['data']['totalcount'];
      }
      unset($_SESSION['glpi_plugin_formcreator_restrictsearchoptions']);
      return $status;
   }

   /**
    *
    */
   public function prepareInputForAdd($input) {
      if (!isset($input['original_id']) || !isset($input['sub_itemtype'])) {
         return false;
      }

      if ($input['sub_itemtype'] == 'PluginFormcreatorForm_Answer') {
         $input['display_id'] = 'f_' . $input['original_id'];
      } else if ($input['sub_itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['original_id'];
      } else {
         return false;
      }

      return $input;
   }

   public function prepareInputForUpdate($input) {
      if (!isset($input['original_id']) || !isset($input['sub_itemtype'])) {
         return false;
      }

      if ($input['sub_itemtype'] == 'PluginFormcreatorForm_Answer') {
         $input['display_id'] = 'f_' . $input['original_id'];
      } else if ($input['sub_itemtype'] == 'Ticket') {
         $input['display_id'] = 't_' . $input['original_id'];
      } else {
         return false;
      }

      return $input;
   }

   static function show($type) {
      global $CFG_GLPI;

      $itemtype = 'PluginFormcreatorIssue';

      $configs = Config::getConfigurationValues('formcreator');
      $params = Search::manageParams($itemtype, $_GET);
      $params['showbookmark'] = false;
      echo "<div class='search_page'>";

      $searchfields = [];
      $columns = [];

      switch($type) {

         case 'myrequest':
            foreach ($params['criteria'] as $index=>$row) {
               if (isset($row['field'])
                     && $row['field'] == 4) {
                  unset($params['criteria'][$index]);
               }
            }

            if (!$configs['is_myrequest_searchengine']) {
               echo '<div style="display: none;">';
            }
            $params['target'] = $CFG_GLPI['root_doc'].'/plugins/formcreator/front/issue.php';
            $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = 'myrequest';
            Search::showGenericSearch($itemtype, $params);
            unset($_SESSION['glpi_plugin_formcreator_restrictsearchoptions']);
            if (!$configs['is_myrequest_searchengine']) {
               echo '</div>';
            }

            $searchfields = importArrayFromDB($configs['myrequest_searchfields']);
            $columns = importArrayFromDB($configs['myrequest_columns']);
            if (!in_array(4, $columns)) {
               $columns[] = 4;
            }
            break;

         case 'allrequest':
            if (!$configs['is_allrequest_searchengine']) {
               echo '<div style="display: none;">';
            }
            $params['target'] = $CFG_GLPI['root_doc'].'/plugins/formcreator/front/allissue.php';
            $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = 'allrequest';
            Search::showGenericSearch($itemtype, $params);
            unset($_SESSION['glpi_plugin_formcreator_restrictsearchoptions']);
            if (!$configs['is_allrequest_searchengine']) {
               echo '</div>';
            }

            $searchfields = importArrayFromDB($configs['allrequest_searchfields']);
            $columns = importArrayFromDB($configs['allrequest_columns']);
            break;

         case 'group':
            foreach ($params['criteria'] as $index=>$row) {
               if (isset($row['field'])
                     && $row['field'] == 71) {
                  unset($params['criteria'][$index]);
               }
            }
            if (isset($_GET['groups_id'])
                  && is_numeric($_GET['groups_id'])) {
               if (!$configs['is_grouprequest_'.$_GET['groups_id'].'_searchengine']) {
                  echo '<div style="display: none;">';
               }
               $params['target'] = $CFG_GLPI['root_doc'].'/plugins/formcreator/front/groupissue.php?groups_id='.$_GET['groups_id'];
               $params['addhidden'] = ['groups_id' => $_GET['groups_id']];
               $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = 'group-'.$_GET['groups_id'];
               Search::showGenericSearch($itemtype, $params);
               unset($_SESSION['glpi_plugin_formcreator_restrictsearchoptions']);
               if (!$configs['is_grouprequest_'.$_GET['groups_id'].'_searchengine']) {
                  echo '</div>';
               }

               $searchfields = importArrayFromDB($configs['grouprequest_'.$_GET['groups_id'].'_searchfields']);
               $columns = importArrayFromDB($configs['grouprequest_'.$_GET['groups_id'].'_columns']);
               if (!in_array(71, $columns)) {
                  $columns[] = 71;
               }
            }
            break;

      }

      // case for after map search
      if (isset($_GET['criteria'])) {
         foreach ($_GET['criteria'] as $criteria) {
            if (($criteria['field'] == 998
                  || $criteria['field'] == 999)
                 && !in_array($criteria['field'], $columns)) {
               $columns[] = $criteria['field'];
            }
         }
      }
      if (in_array(998, $columns)
            && !in_array(83, $columns)) {
         $columns[] = 83;
      }

      if ($params['as_map'] == 1) {
         if (!in_array(83, $columns)) {
            $columns[] = 83;
         }

         $params['criteria'][] = [
            'link'         => 'AND NOT',
            'field'        => ($itemtype == 'Location') ? 21 : 998,
            'searchtype'   => 'contains',
            'value'        => 'NULL'
         ];
         $params['criteria'][] = [
            'link'         => 'AND NOT',
            'field'        => ($itemtype == 'Location') ? 20 : 999,
            'searchtype'   => 'contains',
            'value'        => 'NULL'
         ];
         $params['as_map'] = 1;
      }

      $data = Search::prepareDatasForSearch($itemtype, $params);

      // Add search specifics criteria
      switch($type) {

         case 'myrequest':
            $data['search']['criteria'][] = [
               'link'       => 'AND',
               'field'      => 4,
               'searchtype' => 'equals',
               'value'      => $_SESSION['glpiID']
            ];
            // used for the map
            $params['criteria'][] = [
               'link'         => 'AND',
               'field'        => 4,
               'searchtype' => 'equals',
               'value'      => $_SESSION['glpiID']
            ];
            break;

         case 'allrequest':
            break;

         case 'group':
            $data['search']['criteria'][] = [
               'link'       => 'AND',
               'field'      => 71,
               'searchtype' => 'equals',
               'value'      => $_GET['groups_id']
            ];
            // used for the map
            $params['criteria'][] = [
               'link'         => 'AND',
               'field'        => 71,
               'searchtype' => 'equals',
               'value'      => $_GET['groups_id']
            ];
            break;
      }

      // Modify to force columns to view
      $data['toview'] = [2];
      $data['tocompute'] = [2];
      foreach ($columns as $searchid) {
         if ($searchid != 2) {
            $data['toview'][] = $searchid;
            $data['tocompute'][] = $searchid;
         }
      }
      $data['search']['sort'] = 2;
      $data['itemtype'] = 'Ticket';
      $data['item'] = new Ticket();
      Search::constructSQL($data);

/* for restrict type (type = form)
$sql = $data['sql']['search'];
$sql  = str_replace(' WHERE ', 'LEFT JOIN glpi_plugin_formcreator_issues ON glpi_tickets.id=glpi_plugin_formcreator_issues.original_id WHERE ', $sql);
$sql = str_replace(' GROUP BY ', ' AND glpi_plugin_formcreator_issues.original_id IS NOT NULL GROUP BY ', $sql);
$data['sql']['search'] = $sql;
*/
      Search::constructData($data);
      $data['itemtype'] = 'PluginFormcreatorIssue';

      // change link to ticket to issue of formcreator
      if (isset($data['data']['rows'])) {
         foreach ($data['data']['rows'] as $index_row=>$row) {
            foreach ($row as $index_item=>$item) {
               if (isset($item['displayname'])
                     && strstr($item['displayname'], 'front/ticket.form.php')) {
                  $item['displayname'] = str_replace('front/ticket.form.php?id='.$row['raw']['id'].'"', 'plugins/formcreator/front/issue.form.php?id='.$row['raw']['id'].'&sub_itemtype=Ticket"', $item['displayname']);
                  $data['data']['rows'][$index_row][$index_item] = $item;
               }
            }
         }
      }

      $issue = new PluginFormcreatorIssue();
      $searchoptions = $issue->rawSearchOptions();
      $display_map_link = false;
      switch($type) {

         case 'myrequest':
            foreach ($searchoptions as $row) {
               if (isset($row['id'])
                     && $row['id'] == 4) {
                  unset($row['id']);
                  Search::$search['PluginFormcreatorIssue'][4] = $row;
                  break;
               }
            }
            if ($configs['is_myrequest_map']) {
               $display_map_link = true;
            }
            break;

         case 'allrequest':
            if ($configs['is_allrequest_map']) {
               $display_map_link = true;
            }
            break;

         case 'group':
            foreach ($searchoptions as $row) {
               if (isset($row['id'])
                     && $row['id'] == 71) {
                  unset($row['id']);
                  Search::$search['PluginFormcreatorIssue'][71] = $row;
                  break;
               }
            }
            $data['search']['target'] = $CFG_GLPI['root_doc'].'/plugins/formcreator/front/groupissue.php?groups_id='.$_GET['groups_id'];

            if ($configs['is_grouprequest_'.$_GET['groups_id'].'_map']) {
               $display_map_link = true;
            }
            break;

      }

      // Map link
      if ($display_map_link) {
         echo  "<div class='center'><div class='pager_controls'><label for='as_map'><span title='".__s('Show as map')."' class='pointer fa fa-globe'
            onClick=\"toogle('as_map','','','');
                        document.forms['searchform".$data["itemtype"]."'].submit();\"></span></label></div></div>";
      }

      // Modification to prevent meta, so have column title like: Tickets - ID
      foreach ($data['data']['cols'] as $idx=>$vals) {
         if ($vals['itemtype'] == 'Ticket') {
            $data['data']['cols'][$idx]['itemtype'] = 'PluginFormcreatorIssue';
         }
      }
      Search::displayData($data);

      if ($params['as_map'] == 1
            && $display_map_link) {

         $itemtype = 'Ticket';

         // MAP......
         if ($data['data']['totalcount'] > 0) {
            $target = $data['search']['target'];
            $criteria = $data['search']['criteria'];
            array_pop($criteria);
            array_pop($criteria);
            // required to have the location name in the search list
            $criteria[] = [
               'link'         => 'AND',
               'field'        => ($itemtype == 'Location') ? 1 : ($itemtype == 'Ticket') ? 83 : 3,
               'searchtype'   => 'contains',
               'value'        => '^'
            ];
            $criteria[] = [
               'link'         => 'AND',
               'field'        => ($itemtype == 'Location') ? 21 : 998,
               'searchtype'   => 'contains',
               'value'        => '^LATLOCATION$'
            ];
            $criteria[] = [
               'link'         => 'AND',
               'field'        => ($itemtype == 'Location') ? 20 : 999,
               'searchtype'   => 'contains',
               'value'        => '^LNGLOCATION$'
            ];

            $globallinkto = Toolbox::append_params(
               [
                  'criteria'     => Toolbox::stripslashes_deep($criteria),
                  'metacriteria' => Toolbox::stripslashes_deep($data['search']['metacriteria'])
               ],
               '&amp;'
            );
            $parameters = "as_map=0&amp;sort=".$data['search']['sort']."&amp;order=".$data['search']['order'].'&amp;'.
                           $globallinkto;

            if (strpos($target, '?') == false) {
               $fulltarget = $target."?".$parameters;
            } else {
               $fulltarget = $target."&".$parameters;
            }
            $typename = class_exists($itemtype) ? $itemtype::getTypeName($data['data']['totalcount']) :
                           ($itemtype == 'AllAssets' ? __('assets') : $itemtype);

            echo "<div class='center'><p>".__('Search results for localized items only')."</p>";
            $js = "$(function() {
                  var map = initMap($('#page'), 'map', '600px');
                  _loadMap(map, '$itemtype');
               });

            var _loadMap = function(map_elt, itemtype) {
               L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa';
               var _micon = 'circle';

               var stdMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'blue'
               });

               var aMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'cadetblue'
               });

               var bMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'purple'
               });

               var cMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'darkpurple'
               });

               var dMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'red'
               });

               var eMarker = L.AwesomeMarkers.icon({
                  icon: _micon,
                  markerColor: 'darkred'
               });


               //retrieve geojson data
               map_elt.spin(true);
               $.ajax({
                  dataType: 'json',
                  method: 'POST',
                  url: '{$CFG_GLPI['root_doc']}/ajax/map.php',
                  data: {
                     itemtype: itemtype,
                     params: ".json_encode($params)."
                  }
               }).done(function(data) {
                  var _points = data.points;
                  var _markers = L.markerClusterGroup({
                     iconCreateFunction: function(cluster) {
                        var childCount = cluster.getChildCount();

                        var markers = cluster.getAllChildMarkers();
                        var n = 0;
                        for (var i = 0; i < markers.length; i++) {
                           n += markers[i].count;
                        }

                        var c = ' marker-cluster-';
                        if (n < 10) {
                           c += 'small';
                        } else if (n < 100) {
                           c += 'medium';
                        } else {
                           c += 'large';
                        }

                        return new L.DivIcon({ html: '<div><span>' + n + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
                     }
                  });

                  $.each(_points, function(index, point) {
                     var _title = '<strong>' + point.title + '</strong><br/><a href=\''+'$fulltarget'.replace(/LATLOCATION/, point.lat).replace(/LNGLOCATION/, point.lng)+'\'>".sprintf(__('%1$s %2$s'), 'COUNT', $typename)."'.replace(/COUNT/, point.count)+'</a>';
                     if (point.types) {
                        $.each(point.types, function(tindex, type) {
                           _title += '<br/>".sprintf(__('%1$s %2$s'), 'COUNT', 'TYPE')."'.replace(/COUNT/, type.count).replace(/TYPE/, type.name);
                        });
                     }
                     var _icon = stdMarker;
                     if (point.count < 10) {
                        _icon = stdMarker;
                     } else if (point.count < 100) {
                        _icon = aMarker;
                     } else if (point.count < 1000) {
                        _icon = bMarker;
                     } else if (point.count < 5000) {
                        _icon = cMarker;
                     } else if (point.count < 10000) {
                        _icon = dMarker;
                     } else {
                        _icon = eMarker;
                     }
                     var _marker = L.marker([point.lat, point.lng], { icon: _icon, title: point.title });
                     _marker.count = point.count;
                     _marker.bindPopup(_title);
                     _markers.addLayer(_marker);
                  });

                  map_elt.addLayer(_markers);
                  map_elt.fitBounds(
                     _markers.getBounds(), {
                        padding: [50, 50],
                        maxZoom: 12
                     }
                  );
               }).fail(function (response) {
                  var _data = response.responseJSON;
                  var _message = '".__s('An error occured loading data :(')."';
                  if (_data.message) {
                     _message = _data.message;
                  }
                  var fail_info = L.control();
                  fail_info.onAdd = function (map) {
                     this._div = L.DomUtil.create('div', 'fail_info');
                     this._div.innerHTML = _message + '<br/><span id=\'reload_data\'><i class=\'fa fa-refresh\'></i> ".__s('Reload')."</span>';
                     return this._div;
                  };
                  fail_info.addTo(map_elt);
                  $('#reload_data').on('click', function() {
                     $('.fail_info').remove();
                     _loadMap(map_elt);
                  });
               }).always(function() {
                  //hide spinner
                  map_elt.spin(false);
               });
            }

            ";
            echo Html::scriptBlock($js);
            echo "</div>";
         }
      }
      echo "</div>";

      // hack for the user action when want add a new criteria
      if ($type == 'group') {
         $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = $type.'-'.$_GET['groups_id'];
      } else {
         $_SESSION['glpi_plugin_formcreator_restrictsearchoptions'] = $type;
      }
   }
}
