<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Manage the profiles in plugin.
 */
class PluginFormcreatorProfile extends Profile {

   /**
    * The right name for this class
    *
    * @var string
    */
   static $rightname = "config";


   /**
    * Get the tab name used for item
    *
    * @param object $item the item object
    * @param integer $withtemplate 1 if is a template form
    * @return string name of the tab
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::createTabEntry('Formcreator');
   }


   /**
    * Display the content of the tab
    *
    * @param object $item
    * @param integer $tabnum number of the tab to display
    * @param integer $withtemplate 1 if is a template form
    * @return boolean
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $pfProfile = new self();
      if ($item->fields['interface'] == 'central') {
         $pfProfile->showForm($item->getID());
      }
      return true;
   }


   /**
    * Display form
    *
    * @param integer $profiles_id
    * @return true
    */
   function showForm($profiles_id = 0, $options = []) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      // $rights = $this->getRightsGeneral();
      $rights = [
         ['itemtype'  => 'PluginFusioninventoryUnmanaged',
               'label'     => _n('Form', 'Forms', 2, 'formcreator'),
               'field'     => 'plugin_formcreator_form'],
      ];
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => _n('Form', 'Forms', 1, 'formcreator')]);

      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $this->showLegend();
      return true;
   }



}


