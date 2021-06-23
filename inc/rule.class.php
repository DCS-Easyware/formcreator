<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Manage rules for forms.
 */
class PluginFormcreatorRule extends Rule
{
//    /**
//     * The right name for this class
//     *
//     * @var string
//     */
//    static $rightname = "plugin_formcreator_rule";

    /**
     * Set these rules can be sorted
     *
     * @var boolean
     */
    public $can_sort=true;

    /**
     * Set these rules don't have specific parameters
     *
     * @var boolean
     */
    public $specific_parameters = false;

    static function getTable($classname = null) {
        return parent::getTable(__CLASS__);
    }

    static function getLinkTable() {
        return 'glpi_plugin_formcreator_rules';
    }

    static function getTypeName($nb = 0) {
        return _n('Rule', 'Rules', $nb);
    }

    /**
     * Get name of this type by language of the user connected
     *
     * @return string name of this type
     */
    function getTitle() {
        return ('Pool de règles');
        return __('Rules pools', 'formcreator');
    }


    /**
     * Make some changes before process review result
     *
     * @param array $output
     * @return array
     */
    function preProcessPreviewResults($output) {
        return $output;
    }

    /**
     * Code execution of actions of the rule
     *
     * @param array $output
     * @param array $params
     * @return array
     */
    function executeActions($output, $params, array $input = []) {
        Toolbox::logError('execute');
//        if (count($this->actions)) {
//            foreach ($this->actions as $action) {
//                Toolbox::logError($action);
//            }
//        }
    }

    /**
     * Get the actions available for the rule
     *
     * @return array
     */
    function getActions() {
        global $DB;

        $actions                                              = [];

        $actions['itilcategories_id']['name']                 = __('Category');
        $actions['itilcategories_id']['type']                 = 'dropdown';
        $actions['itilcategories_id']['table']                = 'glpi_itilcategories';

        $actions['type']['name']                              = __('Type');
        $actions['type']['table']                             = 'glpi_tickets';
        $actions['type']['type']                              = 'dropdown_tickettype';

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
        $actions['_groups_id_requester']['force_actions']     = ['assign', 'append', 'fromitem'];
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

        $actions['affectobject']['name']                      = _n('Associated element', 'Associated elements', Session::getPluralNumber());
        $actions['affectobject']['type']                      = 'text';
        $actions['affectobject']['force_actions']             = ['affectbyip', 'affectbyfqdn',
            'affectbymac'];

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

        $actions['responsible_id_validate']['name']                 = sprintf(__('%1$s - %2$s'),
            __('Send an approval request'),
            __('Responsible of the requester'));
        $actions['responsible_id_validate']['type']                 = 'yesno';
        $actions['responsible_id_validate']['force_actions']        = ['add_validation'];

        $actions['groups_id_validate']['name']                = sprintf(__('%1$s - %2$s'),
            __('Send an approval request'),
            __('Group'));
        $actions['groups_id_validate']['type']                = 'dropdown_groups_validate';
        $actions['groups_id_validate']['force_actions']       = ['add_validation'];

        $actions['validation_percent']['name']                = sprintf(__('%1$s - %2$s'),
            __('Send an approval request'),
            __('Minimum validation required'));
        $actions['validation_percent']['type']                = 'dropdown_validation_percent';

        $actions['users_id_validate_requester_supervisor']['name']
            = __('Approval request to requester group manager');
        $actions['users_id_validate_requester_supervisor']['type']
            = 'yesno';
        $actions['users_id_validate_requester_supervisor']['force_actions']
            = ['add_validation'];

        $actions['users_id_validate_assign_supervisor']['name']
            = __('Approval request to technician group manager');
        $actions['users_id_validate_assign_supervisor']['type']
            = 'yesno';
        $actions['users_id_validate_assign_supervisor']['force_actions']
            = ['add_validation'];

        $actions['locations_id']['name']                      = __('Location');
        $actions['locations_id']['type']                      = 'dropdown';
        $actions['locations_id']['table']                     = 'glpi_locations';
        $actions['locations_id']['force_actions']             = ['assign', 'fromuser', 'fromitem'];

        $actions['requesttypes_id']['name']                   = __('Request source');
        $actions['requesttypes_id']['type']                   = 'dropdown';
        $actions['requesttypes_id']['table']                  = 'glpi_requesttypes';

        $actions['takeintoaccount_delay_stat']['name']          = __('Take into account delay');
        $actions['takeintoaccount_delay_stat']['type']          = 'yesno';
        $actions['takeintoaccount_delay_stat']['force_actions'] = ['do_not_compute'];

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
                            ['glpi_plugin_fields_containers.itemtypes' => '["Ticket"]'],
                            ['glpi_plugin_fields_containers.itemtypes' => '["Change"]']
                        ]
                    ],
                ]
            ];
            $found_fields = $DB->request($request);

            foreach ($found_fields as $field) {
                if (preg_match('/"([^"]+)"/', $field['itemtypes'], $matches)) {
                    $actions['fields_' . $field['name']]['name'] = $field['label'];
                    $actions['fields_' . $field['name']]['type'] = $field['type'];
                    $actions['fields_' . $field['name']]['linkfield'] = $field['name'];
                    $tableName = strtolower($matches[1]) . $field['container_name'] . 's';
                    $actions['fields_' . $field['name']]['table'] = 'glpi_plugin_fields_' . $tableName;
                } else {
                    break;
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
    function getCriterias() {

        $criterias = [];

        $criterias['question']['table']        = 'glpi_plugin_formcreator_questions';
        $criterias['question']['field']        = 'name';
        $criterias['question']['name']         = 'Question : Titre';
        $criterias['question']['linkfield']    = 'name';
        $criterias['question']['allow_condition'] = [
            Rule::PATTERN_IS,
            Rule::PATTERN_IS_NOT,
            Rule::PATTERN_CONTAIN ,
            Rule::PATTERN_NOT_CONTAIN,
            Rule::PATTERN_BEGIN,
            Rule::PATTERN_END,
            Rule::REGEX_MATCH,
            Rule::REGEX_NOT_MATCH,
            Rule::PATTERN_UNDER,
            Rule::PATTERN_NOT_UNDER,
            Rule::PATTERN_IS_EMPTY
        ];

        $criterias['answer']['table']        = 'glpi_plugin_formcreator_answers';
        $criterias['answer']['field']        = 'answer';
        $criterias['answer']['name']         = 'Question : Valeur réponse';
        $criterias['answer']['allow_condition'] = [
            Rule::PATTERN_IS,
            Rule::PATTERN_IS_NOT,
            Rule::PATTERN_CONTAIN ,
            Rule::PATTERN_NOT_CONTAIN,
            Rule::PATTERN_BEGIN,
            Rule::PATTERN_END,
            Rule::REGEX_MATCH,
            Rule::REGEX_NOT_MATCH,
            Rule::PATTERN_UNDER,
            Rule::PATTERN_NOT_UNDER,
            Rule::PATTERN_IS_EMPTY
        ];

//        $criterias['visibility']['table']        = 'glpi_plugin_formcreator_answers'; // Todo glpi_plugins_formcreator_questions_conditions
//        $criterias['visibility']['field']        = 'answer'; // Todo OR create field visibility
//        $criterias['visibility']['name']         = 'Question : Visibilité';
//        $criterias['visibility']['allow_condition'] = [
//            Rule::PATTERN_IS,
//            Rule::PATTERN_IS_NOT
//        ];

        return $criterias;
    }

//    function post_addItem() {
//        parent::post_addItem();
//        $this->linkTable($this->fields['id'], $this->input['id'], $this->input['type']);
//    }

    /**
     * Define maximum number of actions possible in a rule
     *
     * @return integer
     */
    function maxActionsCount() {
        return 5;
        return 2;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        return 'Pool de règles';
    }

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
        echo '<a href="javascript:addRule('.$item->getID().', \''.$token.'\');">
                <img src="'.$CFG_GLPI['root_doc'].'/pics/menu_add.png" alt="+" align="absmiddle" />
                '.__('Add a rule', 'formcreator').'
            </a>';
        echo '</td>';
        echo '</tr>';

        echo "</table>";
    }

    public function linkTable($id, $formId, $type) {
        global $DB;
        $DB->query("INSERT INTO ".self::getLinkTable()." (plugin_formcreator_forms_id, rules_id, rules_type) VALUES ($formId, $id, '$type')");
    }

    public function removeFromLinkTable($id) {
        global $DB;
        $DB->query("DELETE FROM ".self::getLinkTable()." WHERE rules_id = $id");
    }

    public function showSubForm($ID) {
        echo '<form name="form_rule" method="post" action="'.static::getFormURL().'">';
        echo '<table class="tab_cadre_fixe">';

        echo '<tr><th colspan="6">'.__('Add a rule', 'formcreator').'</th></tr>';

        echo '<tr class="line1">';
        echo '<td width="10%"><strong>'.__('Name').' <span style="color:red;">*</span></strong></td>';
        echo '<td width="40%"><input type="text" name="name" style="width:90%;" value="" /></td>';
        echo '<td width="10%"><strong>'._n('Match', 'Matches', 1).'</strong></td>';
        echo '<td width="10%">';
        Dropdown::showFromArray('match', [
            ''    => '-----',
            'AND' => 'AND',
            'OR'  => 'OR',
        ]);
        echo '</td>';
        echo '<td width="10%"><strong>'._n('Type', 'Types', 1).' <span style="color:red;">*</span></strong></td>';
        echo '<td width="15%">';
        Dropdown::showFromArray('type', [
            'ticket' => 'Ticket',
            'change'  => 'Changement',
        ]);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="line0">';
        echo '<td colspan="6" class="center">';
        echo '<input type="hidden" name="plugin_formcreator_forms_id" value="'.intval($_REQUEST['form_id']).'" />';
        echo '<input type="submit" name="add" class="submit_button" value="'.__('Add').'" />';
        echo '</td>';
        echo '</tr>';

        echo '</table>';
        Html::closeForm();
    }

    /**
     * Prepare input data for adding the question
     * Check fields values and get the order for the new question
     *
     * @param array $input data used to add the item
     *
     * @return array the modified $input array
     */
    public function prepareInputForAdd($input) {
        if (isset($input['name'])
            && empty($input['name'])) {
            Session::addMessageAfterRedirect(__('The name cannot be empty!', 'formcreator'), false, ERROR);
            return [];
        }

//        if (isset($input['match'])
//            && empty($input['match'])) {
//            $input['match'] = 'AND';
//        }

        $input['sub_type'] = 'PluginFormcreatorRule';

        return $input;
    }
}