<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Manage rules for forms.
 */
class PluginFormcreatorRule extends Rule
{
    public $can_sort = true;

    public $specific_parameters = false;

    static $rightname = 'entity';

    public $itemtype = 'PluginFormcreatorRule';

    static function getTable($classname = null) {
        return parent::getTable(__CLASS__);
    }

    static function getLinkTable() {
        return 'glpi_plugin_formcreator_rules';
    }

    static function getTypeName($nb = 0) {
        if ($nb === 0) {
            return 'Règle';
        }
        return 'Règles';
    }

    /**
     * Make some changes before process review result
     *
     * @param array $output
     *
     * @return array
     */
    public function preProcessPreviewResults($output) {
        return $output;
    }

    /**
     * Display more actions
     *
     * @param array $action
     * @param string $value
     * @return boolean
     */
    function displayAdditionalRuleAction(array $action, $value = '') {
        if ($action['type'] == 'textarea') {
            Html::textarea([
                'name'            => 'value',
                'enable_richtext' => true,
                'cols'            => 100,
                'rows'            => 10
            ]);
        }
        return true;
    }

    /**
     * Get the actions available for the rule
     *
     * @return array
     */
    public function getActions() {
        global $DB;

        $request = [
            'SELECT' => ['rules_type'],
            'FROM' => [self::getLinkTable()],
            'WHERE' => [
                [self::getLinkTable() . '.rules_id' => $this->getField('id')]
            ]
        ];
        $type = $DB->request($request)->next()['rules_type'];

        $actions                                              = [];

        $actions['fullform']['name']                          = __('Formulaire complet');
        $actions['fullform']['type']                          = 'yesonly';
        $actions['fullform']['force_actions']                 = ['assign'];

//        $actions['affectobject']['name']                      = _n('Associated element', 'Associated elements', Session::getPluralNumber());
//        $actions['affectobject']['type']                      = 'dropdown_management';
//        $actions['affectobject']['force_actions']             = ['affectbyip', 'affectbyfqdn',
//            'affectbymac'];

        $actions['name']['name']                              = __('Title');
        $actions['name']['force_actions']                     = ['assign', 'append', 'regex_result', 'append_regex_result'];

        $actions['content']['name']                           = __('Content');
        $actions['content']['force_actions']                  = ['assign', 'append', 'regex_result', 'append_regex_result'];
        $actions['content']['type']                           = 'textarea';

        $actions['itilcategories_id']['name']                 = __('Category');
        $actions['itilcategories_id']['type']                 = 'dropdown';
        $actions['itilcategories_id']['table']                = 'glpi_itilcategories';

        $actions['type']['name']                              = __('Type');
        $actions['type']['table']                             = 'glpi_tickets';
        $actions['type']['type']                              = 'dropdown_tickettype';

        $actions['entities_id']['name']                       = Entity::getTypeName(1);
        $actions['entities_id']['type']                       = 'dropdown';
        $actions['entities_id']['table']                      = 'glpi_entities';

        $actions['entity_current']['name']                    = __('Current active entity', 'formcreator');
        $actions['entity_current']['type']                    = 'yesonly';

        $actions['entity_requester']['name']                  = __("Default requester user's entity", 'formcreator');
        $actions['entity_requester']['type']                  = 'yesonly';

//        $actions['entity_requester_dyn_first']['name']        = __("First dynamic requester user's entity (alphabetical)", 'formcreator');
//        $actions['entity_requester_dyn_first']['type']        = 'yesonly';
//
//        $actions['entity_requester_dyn_last']['name']        = __("Last dynamic requester user's entity (alphabetical)", 'formcreator');
//        $actions['entity_requester_dyn_last']['type']        = 'yesonly';

        $actions['entity_form']['name']                      = __('The form entity', 'formcreator');
        $actions['entity_form']['type']                      = 'yesonly';

        $actions['entity_validator']['name']                 = __('Default entity of the validator', 'formcreator');
        $actions['entity_validator']['type']                 = 'yesonly';

        $actions['entity_user']['name']                      = __('Default entity of a user type question answer', 'formcreator');
        $actions['entity_user']['type']                      = 'yesonly';

//        $actions['entity_entity']['name']                    = __('From a GLPI object > Entity type question answer', 'formcreator');
//        $actions['entity_entity']['type']                    = 'yesonly';

        $actions['_users_id_requester']['name']               = __('Requester');
        $actions['_users_id_requester']['type']               = 'dropdown_users';
        $actions['_users_id_requester']['force_actions']      = ['assign', 'append'];
        $actions['_users_id_requester']['permitseveral']      = ['append'];
        $actions['_users_id_requester']['appendto']           = '_additional_requesters';
        $actions['_users_id_requester']['appendtoarray']      = ['use_notification' => 1];
        $actions['_users_id_requester']['appendtoarrayfield'] = 'users_id';

        $actions['_groups_id_requester']['name']              = __('Requester group');
        $actions['_groups_id_requester']['type']              = 'dropdown';
        $actions['_groups_id_requester']['table']             = 'glpi_groups';
        $actions['_groups_id_requester']['condition']         = '`is_requester`';
        $actions['_groups_id_requester']['force_actions']     = ['assign', 'append'];
        $actions['_groups_id_requester']['permitseveral']     = ['append'];
        $actions['_groups_id_requester']['appendto']          = '_additional_groups_requesters';

        $actions['_users_id_assign']['name']                  = __('Technician');
        $actions['_users_id_assign']['type']                  = 'dropdown_assign';
        $actions['_users_id_assign']['force_actions']         = ['assign', 'append'];
        $actions['_users_id_assign']['permitseveral']         = ['append'];
        $actions['_users_id_assign']['appendto']              = '_additional_assigns';
        $actions['_users_id_assign']['appendtoarray']         = ['use_notification' => 1];
        $actions['_users_id_assign']['appendtoarrayfield']    = 'users_id';

        $actions['_groups_id_assign']['table']                = 'glpi_groups';
        $actions['_groups_id_assign']['name']                 = __('Technician group');
        $actions['_groups_id_assign']['type']                 = 'dropdown';
        $actions['_groups_id_assign']['condition']            = '`is_assign`';
        $actions['_groups_id_assign']['force_actions']        = ['assign', 'append'];
        $actions['_groups_id_assign']['permitseveral']        = ['append'];
        $actions['_groups_id_assign']['appendto']             = '_additional_groups_assigns';

        $actions['_suppliers_id_assign']['table']             = 'glpi_suppliers';
        $actions['_suppliers_id_assign']['name']              = __('Assigned to a supplier');
        $actions['_suppliers_id_assign']['type']              = 'dropdown';
        $actions['_suppliers_id_assign']['force_actions']     = ['assign', 'append'];
        $actions['_suppliers_id_assign']['permitseveral']     = ['append'];
        $actions['_suppliers_id_assign']['appendto']          = '_additional_suppliers_assigns';
        $actions['_suppliers_id_assign']['appendtoarray']     = ['use_notification' => 1];
        $actions['_suppliers_id_assign']['appendtoarrayfield']  = 'suppliers_id';

        $actions['_users_id_observer']['name']                = __('Watcher');
        $actions['_users_id_observer']['type']                = 'dropdown_users';
        $actions['_users_id_observer']['force_actions']       = ['assign', 'append'];
        $actions['_users_id_observer']['permitseveral']       = ['append'];
        $actions['_users_id_observer']['appendto']            = '_additional_observers';
        $actions['_users_id_observer']['appendtoarray']       = ['use_notification' => 1];
        $actions['_users_id_observer']['appendtoarrayfield']  = 'users_id';

        $actions['_groups_id_observer']['table']              = 'glpi_groups';
        $actions['_groups_id_observer']['name']               = __('Watcher group');
        $actions['_groups_id_observer']['type']               = 'dropdown';
        $actions['_groups_id_observer']['condition']          = '`is_watcher`';
        $actions['_groups_id_observer']['force_actions']      = ['assign', 'append'];
        $actions['_groups_id_observer']['permitseveral']      = ['append'];
        $actions['_groups_id_observer']['appendto']           = '_additional_groups_observers';

        $actions['urgency']['name']                           = __('Urgency');
        $actions['urgency']['type']                           = 'dropdown_urgency';

        $actions['impact']['name']                            = __('Impact');
        $actions['impact']['type']                            = 'dropdown_impact';

        $actions['priority']['name']                          = __('Priority');
        $actions['priority']['type']                          = 'dropdown_priority';
        $actions['priority']['force_actions']                 = ['assign', 'compute'];

        $actions['status']['name']                            = __('Status');
        $actions['status']['type']                            = 'dropdown_status';

        $actions['slas_id_ttr']['table']                      = 'glpi_slas';
        $actions['slas_id_ttr']['field']                      = 'name';
        $actions['slas_id_ttr']['name']                       = sprintf(__('%1$s %2$s'), __('SLA'),
            __('Time to resolve'));
        $actions['slas_id_ttr']['linkfield']                  = 'slas_id_ttr';
        $actions['slas_id_ttr']['type']                       = 'dropdown';
        $actions['slas_id_ttr']['condition']                  = "`glpi_slas`.`type` = '".SLM::TTR."'";

        $actions['slas_id_tto']['table']                      = 'glpi_slas';
        $actions['slas_id_tto']['field']                      = 'name';
        $actions['slas_id_tto']['name']                       = sprintf(__('%1$s %2$s'), __('SLA'),
            __('Time to own'));
        $actions['slas_id_tto']['linkfield']                  = 'slas_id_tto';
        $actions['slas_id_tto']['type']                       = 'dropdown';
        $actions['slas_id_tto']['condition']                  = "`glpi_slas`.`type` = '".SLM::TTO."'";

        $actions['olas_id_ttr']['table']                      = 'glpi_olas';
        $actions['olas_id_ttr']['field']                      = 'name';
        $actions['olas_id_ttr']['name']                       = sprintf(__('%1$s %2$s'), __('OLA'),
            __('Time to resolve'));
        $actions['olas_id_ttr']['linkfield']                  = 'olas_id_ttr';
        $actions['olas_id_ttr']['type']                       = 'dropdown';
        $actions['olas_id_ttr']['condition']                  = "`glpi_olas`.`type` = '".SLM::TTR."'";

        $actions['olas_id_tto']['table']                      = 'glpi_olas';
        $actions['olas_id_tto']['field']                      = 'name';
        $actions['olas_id_tto']['name']                       = sprintf(__('%1$s %2$s'), __('OLA'),
            __('Time to own'));
        $actions['olas_id_tto']['linkfield']                  = 'olas_id_tto';
        $actions['olas_id_tto']['type']                       = 'dropdown';
        $actions['olas_id_tto']['condition']                  = "`glpi_olas`.`type` = '".SLM::TTO."'";

        $actions['users_id_validate']['name']                 = sprintf(__('%1$s - %2$s'),
            __('Send an approval request'),
            __('User'));
        $actions['users_id_validate']['type']                 = 'dropdown_users_validate';
        $actions['users_id_validate']['force_actions']        = ['add_validation'];

        $actions['locations_id']['name']                      = __('Location');
        $actions['locations_id']['type']                      = 'dropdown';
        $actions['locations_id']['table']                     = 'glpi_locations';
        $actions['locations_id']['force_actions']             = ['assign', 'fromuser'];

        $actions['requesttypes_id']['name']                   = __('Request source');
        $actions['requesttypes_id']['type']                   = 'dropdown';
        $actions['requesttypes_id']['table']                  = 'glpi_requesttypes';

        $actions['takeintoaccount_delay_stat']['name']          = __('Take into account delay');
        $actions['takeintoaccount_delay_stat']['type']          = 'yesno';
        $actions['takeintoaccount_delay_stat']['force_actions'] = ['do_not_compute'];

        if ($type === 'change') {
            unset($actions['takeintoaccount_delay_stat']);
            unset($actions['type']);
            unset($actions['locations_id']);
            unset($actions['requesttypes_id']);

            $actions['impactcontent']['name']                          = 'Impacts';
            $actions['impactcontent']['type']                          = 'textarea';
            $actions['impactcontent']['force_actions']                 = ['assign'];

            $actions['controllistcontent']['name']                     = 'Liste de contrôles';
            $actions['controllistcontent']['type']                     = 'textarea';
            $actions['controllistcontent']['force_actions']            = ['assign'];

            $actions['rolloutplancontent']['name']                     = 'Plan de déploiement';
            $actions['rolloutplancontent']['type']                     = 'textarea';
            $actions['rolloutplancontent']['force_actions']            = ['assign'];

            $actions['backoutplancontent']['name']                     = 'Plan de repli';
            $actions['backoutplancontent']['type']                     = 'textarea';
            $actions['backoutplancontent']['force_actions']            = ['assign'];

            $actions['checklistcontent']['name']                       = 'Liste de vérifications';
            $actions['checklistcontent']['type']                       = 'textarea';
            $actions['checklistcontent']['force_actions']              = ['assign'];
        } else {
            if (Plugin::isPluginLoaded('fields')) {
                $request = [
                    'SELECT' => [
                        'glpi_plugin_fields_fields.name',
                        'glpi_plugin_fields_fields.label',
                        'glpi_plugin_fields_fields.type',
                        'glpi_plugin_fields_containers.name as container_name',
                        'glpi_plugin_fields_containers.itemtypes'
                    ],
                    'FROM' => [
                        'glpi_plugin_fields_fields',
                    ],
                    'LEFT JOIN' => [
                        'glpi_plugin_fields_containers' => [
                            'FKEY' => [
                                'glpi_plugin_fields_fields' => 'plugin_fields_containers_id',
                                'glpi_plugin_fields_containers' => 'id',
                            ],
                        ],
                    ],
                    'WHERE' => [
                        [
                            'OR' => [
                                ['glpi_plugin_fields_containers.itemtypes' => '["Ticket"]']
                            ]
                        ],
                    ]
                ];
                $found_fields = $DB->request($request);

                foreach ($found_fields as $field) {
                    if (preg_match('/"([^"]+)"/', $field['itemtypes'], $matches)) {
                        $actions['fields_' . $field['name']]['name'] = __("Additionnal fields", "fields") . ' : ' . $field['label'];

                        $actions['fields_' . $field['name']]['type'] = $field['type'];

                        if ($field['type'] === 'dropdownuser') {
                            $actions['fields_' . $field['name']]['type'] = 'dropdown_users';
                        }

                        $actions['fields_' . $field['name']]['linkfield'] = $field['name'];

                        $tableName = strtolower($matches[1]) . $field['container_name'] . 's';
                        if ($field['type'] === 'dropdown') {
                            $actions['fields_' . $field['name']]['table'] = 'glpi_plugin_fields_' . $field['name'] . 'dropdowns';
                        } else {
                            $actions['fields_' . $field['name']]['table'] = 'glpi_plugin_fields_' . $tableName;
                        }

                        if ($field['type'] === 'dropdownoperatingsystems') {
                            $actions['fields_' . $field['name']]['type'] = 'dropdown';
                            $actions['fields_' . $field['name']]['table'] = 'glpi_operatingsystems';
                        }

                        if ($field['type'] === 'text') {
                            unset($actions['fields_' . $field['name']]['type']);
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * Get the criteria available for the rule
     *
     * @return array
     */
    public function getCriterias() {

        $criterias = [];

        $criterias['question']['table']        = 'glpi_plugin_formcreator_questions';
        $criterias['question']['field']        = 'name';
        $criterias['question']['name']         = 'Question : Titre';
        $criterias['question']['linkfield']    = 'name';
//        $criterias['question']['allow_condition'] = [
//            Rule::PATTERN_IS,
//            Rule::PATTERN_IS_NOT,
//            Rule::PATTERN_CONTAIN ,
//            Rule::PATTERN_NOT_CONTAIN,
//            Rule::PATTERN_BEGIN,
//            Rule::PATTERN_END,
//            Rule::REGEX_MATCH,
//            Rule::REGEX_NOT_MATCH,
//            Rule::PATTERN_UNDER,
//            Rule::PATTERN_NOT_UNDER,
//            Rule::PATTERN_IS_EMPTY
//        ];

        $criterias['answer']['table']        = 'glpi_plugin_formcreator_answers';
        $criterias['answer']['field']        = 'answer';
        $criterias['answer']['name']         = 'Question : Valeur réponse';

        $criterias['visibility']['field']        = 'visibility';
        $criterias['visibility']['name']         = 'Question : Visibilité';
        $criterias['visibility']['type']         = 'yesno';
        $criterias['visibility']['allow_condition'] = [
            Rule::PATTERN_IS
        ];

        return $criterias;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return 'Pool de règles';
    }

//    /**
//     * Define maximum number of actions possible in a rule
//     *
//     * @return integer
//     */
//    function maxActionsCount() {
//        return 10;
//    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        global $CFG_GLPI, $DB;

        echo '<table class="tab_cadre_fixe">';

        echo '<tr>';
        echo '<th colspan="4">'._n('Pools de règles', 'Pools de règles', 2, 'formcreator').'</th>';
        echo '</tr>';

        $token = Session::getNewCSRFToken();
        $i = 0;

        // Display existing rules for this form
        $ruleTable = PluginFormcreatorRule::getTable();
        $ruleLinkTable = PluginFormcreatorRule::getLinkTable();
        $request = [
            'FROM' => [
                $ruleTable,
            ],
            'LEFT JOIN' => [
                $ruleLinkTable => [
                    'FKEY' => [
                        $ruleTable => 'id',
                        $ruleLinkTable => 'rules_id',
                    ],
                ],
            ],
            'WHERE' => [
                $ruleLinkTable.'.plugin_formcreator_forms_id' => $item->getID()
            ]
        ];
        $found_rules = $DB->request($request);

        foreach ($found_rules as $rule) {
//            Toolbox::logError('rule', $rule);
            $i++;
            echo '<tr class="line'.($i % 2).'">';
            $ruleItemUrl = Toolbox::getItemTypeFormURL('rule') . '?id=' . $rule['rules_id'];
            echo '<td onclick="document.location=\'/plugins/formcreator' . $ruleItemUrl . '\'" style="cursor: pointer">';
            echo $rule['name'];
            echo '</td>';

            echo '<td align="center" width="32">';
            echo $rule['match'];
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/edit.png"
                  alt="*" title="'.__('Edit').'" ';
            echo 'onclick="document.location=\'/plugins/formcreator' . $ruleItemUrl . '\'" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '<td align="center" width="32">';
            echo '<img src="'.$CFG_GLPI['root_doc'].'/plugins/formcreator/pics/delete.png"
                  alt="*" title="'.__('Delete', 'formcreator').'"
                  onclick="deleteRule('.$item->getID().', \''.$token.'\', '.$rule['rules_id'].')" align="absmiddle" style="cursor: pointer" /> ';
            echo '</td>';

            echo '</tr>';
        }

        // Display add rule pop up
        echo '<tr class="line'.(($i + 1) % 2).'" id="add_rule_row">';
        echo '<td colspan="4">';
        echo '<a href="javascript:addRule('.$item->getID().', \''.$token.'\', '.$i.');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                Ajouter une règle
            </a>';
        echo '</td>';
        echo '</tr>';

        echo "</table>";
    }

    /**
     * Add or update rules in rules/rulespools link table @see glpi_plugin_formcreator_rules
     *
     * @param $id
     * @param $poolId
     * @param $type
     * @param $action
     *
     * @throws GlpitestSQLError
     */
    public function linkTable($id, $poolId, $type, $action) {
        global $DB;
        if ($action === 'add') {
            $DB->query("INSERT INTO ".self::getLinkTable()." (rules_id, rules_pool_id, rules_type) VALUES ($id, $poolId, '$type')");
        } elseif ($action === 'update') {
            $DB->query("UPDATE ".self::getLinkTable()." SET rules_type = '$type', rules_pool_id = $poolId WHERE plugin_formcreator_forms_id = $formId AND rules_id = $id");
        }
    }

    /**
     * Remove rule from rules/rulespool link table @see glpi_plugin_formcreator_rules
     *
     * @param $id
     *
     * @return bool|mysqli_result
     *
     * @throws GlpitestSQLError
     */
    public function removeFromLinkTable($id) {
        global $DB;
        return $DB->query("DELETE FROM ".self::getLinkTable()." WHERE rules_id = $id");
    }

    public function showSubForm() {
        echo '<form name="form_rule" method="post" action="'.static::getFormURL().'">';
        echo '<table class="tab_cadre_fixe">';

        echo '<tr><th colspan="8">Ajouter une règle</th></tr>';

        echo '<tr class="line1">';
        echo '<td width="5%%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
        echo '<td width="40%"><input type="text" name="name" style="width:90%;" value="" /></td>';
        echo '<td width="5%"><strong>'._n('Match', 'Matches', 1).'</strong></td>';
        echo '<td width="15%">';
        $matches = ['' => '-----'];
        $matches['AND'] = 'AND';
        $matches['OR'] = 'OR';
        Dropdown::showFromArray('match', $matches);
        echo '</td>';
        echo '<td width="5%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
        echo '<td width="20%">';
        Dropdown::showFromArray('type', [
            'ticket' => 'Ticket',
            'change'  => 'Changement',
        ]);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="line0">';
        echo '<td colspan="8" class="center">';
        echo '<input type="hidden" name="plugin_formcreator_rulepools_id" value="'.intval($_REQUEST['pool_id']).'" />';
        echo '<input type="submit" name="add" class="submit_button" value="'.__('Add').'" />';
        echo '</td>';
        echo '</tr>';

        echo '</table>';
        Html::closeForm();
    }

    /**
     * @param array $input data used to add the item
     *
     * @return array the modified $input array
     */
    public function prepareInputForAdd($input): array {
        if (isset($input['name'])
            && empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
        }

        $input['sub_type'] = 'PluginFormcreatorRule';

        return $input;
    }
}