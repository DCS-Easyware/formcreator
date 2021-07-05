<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginFormcreatorRulePool extends CommonDropdown
{
    static $rightname = 'entity';

    public $itemtype = 'PluginFormcreatorRulePool';

    public $can_sort = true;

    public $dohistory = true;

    static function getTable($classname = null) {
        return parent::getTable(__CLASS__);
    }

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return self::getTypeName();
    }

    /**
     * @return string
     */
    static function getMenuName() {
        return self::getTypeName();
    }

    /**
     * @return string name of this type
     */
    public function getTitle() {
        return 'Pools de règles';
    }

    /**
     * @return string
     */
    static function getType()
    {
        return 'PluginFormcreatorRulePool';
    }

    /**
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        if ($nb === 0) {
            return 'Pool de règles';
        }

        return 'Pools de règles';
    }

    /**
     * Get search function for the class
     *
     * @return array of search option
     */
    function rawSearchOptions(): array
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '3',
            'table'              => 'glpi_plugin_formcreator_forms',
            'field'              => 'name',
            'name'               => __('Form', 'formcreator'),
            'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    /**
     * Filter the rules list when display it from a rules pool ("Lister les règles")
     *
     * @param $poolId
     * @return bool
     * @throws GlpitestSQLError
     */
    public function cleanRules($poolId) {
        global $DB;
        $rule = new PluginFormcreatorRule();

        $query = [
            'FROM' => 'glpi_rules',
            'LEFT JOIN' =>  [
                'glpi_plugin_formcreator_rules' => [
                    'FKEY' => [
                        'glpi_rules' => 'id',
                        'glpi_plugin_formcreator_rules' => 'rules_id',
                    ]
                ]
            ],
            'WHERE' => [
                'rules_pool_id' => $poolId
            ]
        ];

        $result = $DB->request($query);

        foreach ($result as $res) {
            $id = $res['rules_id'];
            if ($DB->query("DELETE FROM glpi_rules WHERE id = $id")) {
                $rule->removeFromLinkTable($res['rules_id']);
            }
        }

        return true;
    }

    /**
     * @param $id
     * @return mixed
     * @throws GlpitestSQLError
     */
    public function countRules($id) {
        global $DB;
        $query = 'SELECT COUNT(glpi_plugin_formcreator_rules.rules_id) FROM glpi_plugin_formcreator_rules LEFT JOIN glpi_rules ON glpi_rules.id = glpi_plugin_formcreator_rules.rules_pool_id WHERE glpi_plugin_formcreator_rules.rules_pool_id=' . $id;
        return $DB->query($query)->fetch_assoc()['COUNT(glpi_plugin_formcreator_rules.rules_id)'];
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI, $DB;

        echo '<table class="tab_cadre_fixe">';

        echo '<tr>';
        echo '<th colspan="4">' . _n('Pools de règles', 'Pools de règles', 2, 'formcreator') . '</th>';
        echo '</tr>';

        $token = Session::getNewCSRFToken();
        $i = 0;

        // Display existing rules pools for this form
        $rulePoolTable = PluginFormcreatorRulePool::getTable();
        $request = [
            'FROM' => [
                $rulePoolTable,
            ],
            'WHERE' => [
                $rulePoolTable . '.plugin_formcreator_forms_id' => $item->getID()
            ]
        ];
        $found_rules_pools = $DB->request($request);

        foreach ($found_rules_pools as $rules_pool) {
            $i++;
            echo '<tr class="line' . ($i % 2) . '">';
            $rulePoolItemUrl = Toolbox::getItemTypeFormURL('rulepool') . '?id=' . $rules_pool['id'];
            echo '<td onclick="document.location=\'/plugins/formcreator' . $rulePoolItemUrl . '\'" style="cursor: pointer">';
            echo $rules_pool['name'];
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/edit.png"
                  alt="*" title="' . __('Edit') . '" ';
            echo 'onclick="document.location=\'/plugins/formcreator' . $rulePoolItemUrl . '\'" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="' . $CFG_GLPI['root_doc'] . '/plugins/formcreator/pics/delete.png"
                  alt="*" title="' . __('Delete', 'formcreator') . '"
                  onclick="deleteRulesPool(' . $item->getID() . ', \'' . $token . '\', ' . $rules_pool['id'] . ')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '</tr>';
        }

        // Display add rules pool pop up
        echo '<tr class="line' . (($i + 1) % 2) . '" id="add_rule_row">';
        echo '<td colspan="4">';
        echo '<a href="javascript:addRulesPool(' . $item->getID() . ', \'' . $token . '\', ' . $i . ');">
                <img src="' . $CFG_GLPI['root_doc'] . '/pics/menu_add.png" alt="+" align="absmiddle" />
                Ajouter un pool de règles
            </a>';
        echo '</td>';
        echo '</tr>';

        echo "</table>";
    }

    public function showForm($id, $options = []) {
        global $CFG_GLPI;
        if (!$this->isNewID($id)) {
            $this->check($id, READ);
        } else {
            $this->checkGlobal(UPDATE);
        }

        $token = Session::getNewCSRFToken();

        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__('Name')."</td>";
        echo "<td>";
        Html::autocompletionTextField($this, "name");
        echo "</td>";
        echo "<td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>".__('Comment')."</td>";
        echo "<td>";
        Html::textarea(['name'              => 'comment',
            'value'             => $this->fields["comment"],
            'cols'              => 100,
            'rows'              => 10]);
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='15%'>";
        echo '<a href="javascript:addRuleForPool('.$id.', \''.$token.'\');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                Ajouter une règle
            </a>';
        echo "</td>";
        echo "<td>";
        $nb = $this->countRules($id);
        $url = $CFG_GLPI["root_doc"]."/plugins/formcreator/front/rule.php?pool_id=".$id;
        if ($nb > 0) {
            echo '<a href="'.$url.'">Liste des règles - '.$nb.'</a>';
        }
        echo "</td></tr>";

        $this->showFormButtons($options);

        return true;
    }

    public function showSubForm() {
        echo '<form name="form_rule" method="post" action="'.static::getFormURL().'">'; // TODO : verify
        echo '<table class="tab_cadre_fixe">';

        echo '<tr><th colspan="8">Ajouter un pool de règles</th></tr>';

        echo '<tr class="line1">';
        echo '<td width="5%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
        echo '<td width="30%"><input type="text" name="name" style="width:90%;" value="" /></td>';
        echo '<td width="5%"><strong>'.__('Comment').'</strong></td>';
        echo '<td width="60%"><input type="text" name="comment" style="width:90%;" value="" /></td>';

        echo '<tr class="line0">';
        echo '<td colspan="8" class="center">';
        echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.intval($_REQUEST['form_id']).'" />';
        echo '<input type="submit" name="add" class="submit_button" value="'.__('Add').'" />';
        echo '</td>';
        echo '</tr>';

        echo '</table>';
        Html::closeForm();
    }
}