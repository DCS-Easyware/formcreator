<?php

class PluginFormcreatorRuleCollection extends RuleCollection
{
    static $rightname = 'config';

    public $itemtype = 'PluginFormcreatorRuleCollection';

//    /**
//     * Set stop play rules when have the first rule of list match
//     *
//     * @var boolean
//     */
//    public $stop_on_first_match = true;

    /**
     * Get name of this type by language of the user connected
     *
     * @return string name of this type
     */
    function getTitle() {
        return ('Pool de règles');
    }

    static function getType()
    {
        return 'PluginFormcreatorRuleCollection';
    }

    static function getTypeName($nb = 0) {
        return __('Rules pools', 'formcreator');
    }

//    /**
//     * Get content menu breadcrumb
//     *
//     * @return array
//     */
//    static function getMenuContent() {
//        Toolbox::logError('get menu content rule collection');
//        $menu = [];
//        if (Session::haveRight(static::$rightname, READ)) {
//            $menu['title']           = self::getTypeName();
//            $menu['page']            = self::getSearchURL(false);
//            $menu['links']['search'] = self::getSearchURL(false);
//        }
//        return $menu;
//    }

    function getRuleListCriteria($options = []) {
        $rules = parent::getRuleListCriteria();

        Toolbox::logError($_SESSION['plugin_formcreator_pool_id']);
        if (isset($_SESSION['plugin_formcreator_pool_id'])) {
            $rules['LEFT JOIN'] = [
                'glpi_plugin_formcreator_rules' => [
                    'FKEY' => [
                        'glpi_rules' => 'id',
                        'glpi_plugin_formcreator_rules' => 'rules_id',
                    ],
                ],
            ];
            $rules['WHERE']['rules_pool_id'] = $_SESSION['plugin_formcreator_pool_id'];
        }

        return $rules;
    }

    /**
     * Print a title if needed which will be displayed above list of rules
     **/
    function title() {
        global $DB;

        if (isset($_SESSION['plugin_formcreator_pool_id'])) {
            $query = "SELECT name FROM glpi_plugin_formcreator_rulepools WHERE id=".$_SESSION['plugin_formcreator_pool_id'];
            $pool_name = $DB->query($query)->fetch_assoc()['name'];

            echo '<h3>'.$pool_name.'</h3>';
            echo '<br>';
        }
    }
}