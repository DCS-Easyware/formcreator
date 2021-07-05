<?php

class PluginFormcreatorRuleCollection extends RuleCollection
{
    static $rightname = 'entity';

    public $itemtype = 'PluginFormcreatorRuleCollection';

    /**
     * Get name of this type by language of the user connected
     *
     * @return string name of this type
     */
    function getTitle() {
        return 'Pools de règles';
    }

    static function getType()
    {
        return 'PluginFormcreatorRuleCollection';
    }

    static function getTypeName($nb = 0) {
        if ($nb === 0) {
            return 'Pool de règles';
        }
        return 'Pools de règles';
    }

    function getRuleListCriteria($options = []) {
        $rules = parent::getRuleListCriteria();

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