<?php

class PluginFormcreatorUpgradeTo2_11_3
{
    /** @var Migration */
    protected $migration;

    /**
     * @param Migration $migration
     *
     * @throws GlpitestSQLError
     */
    public function upgrade(Migration $migration)
    {
        $this->migration = $migration;

        if ($this->createRulesPoolsTables()) {
            $this->upgradeTicketTargets();
            $this->upgradeChangeTargets();
        }
    }

    private function createRulesPoolsTables() {
        global $DB;

        $queryRules = 'CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_rules` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `plugin_formcreator_forms_id`   int(11)       NOT NULL,
   `rules_pool_id`                 int(11)       DEFAULT NULL,
   `rules_id`                      varchar(255)  DEFAULT NULL,
   `rules_type`                    varchar(50)   DEFAULT NULL,
   PRIMARY KEY (`id`),
   INDEX `plugin_formcreator_forms_id` (`plugin_formcreator_forms_id`),
   INDEX `rules_id` (`rules_id`),
   INDEX `rules_pool_id` (`rules_pool_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $queryRulesPools = 'CREATE TABLE IF NOT EXISTS `glpi_plugin_formcreator_rulepools` (
       `id` INT(11) auto_increment NOT NULL,
       `name`                          varchar(100)  NOT NULL,
       `comment`                       varchar(255)  NULL,
       `plugin_formcreator_forms_id`   int(11)       NOT NULL,
       PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        $success = $DB->query($queryRules);
        if (!$success) {
            return false;
        }

        return $DB->query($queryRulesPools);
    }

    /**
     * @throws GlpitestSQLError
     */
    private function upgradeTicketTargets() {
        global $DB;

        $query = 'SELECT * FROM  glpi_plugin_formcreator_targettickets';
        $result = $DB->query($query);
        foreach ($result as $target) {
            $target['name'] = str_replace("'", "\'", $target['name']);
            $target['content'] = str_replace("'", "\'", $target['content']);

            // Add a rule pool
            $rulePoolQuery = "INSERT INTO glpi_plugin_formcreator_rulepools (name, plugin_formcreator_forms_id, comment) 
                VALUES ('". $target['name'] ."_pool', ". $target['plugin_formcreator_forms_id'] .", 'Migration de la cible ". $target['name'] ."')";
            $DB->query($rulePoolQuery);
            $rulePoolId = $DB->insertId();


            // Create a rule and link it to rulepool
            $ruleQuery = "INSERT INTO glpi_rules (entities_id, sub_type, name, is_active, description) 
                VALUES (0, 'PluginFormcreatorRule','". $target['name'] ."_rule', 1, 'Migration de la cible ". $target['name'] ."')";
            $DB->query($ruleQuery);
            $ruleId = $DB->insertId();

            $linkQuery = "INSERT INTO glpi_plugin_formcreator_rules (plugin_formcreator_forms_id, rules_pool_id, rules_id, rules_type) 
                VALUES (" . $target['plugin_formcreator_forms_id'] .", ". $rulePoolId .", " . $ruleId .", 'ticket')";
            $DB->query($linkQuery);


            // Add actions to rule
            // Title
            if ($target['target_name'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'name', '" . $target['target_name'] ."')";
                $DB->query($actionQuery);
            }

            // Content & Fullform
            if (!!strpos($target['content'], '##FULLFORM##')) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'fullform', 1)";
                $DB->query($actionQuery);
            } elseif ($target['content'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'name', '" . $target['content'] ."')";
                $DB->query($actionQuery);
            }

            // Category
            if ($target['category_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'itilcategories_id', " . $target['category_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['category_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'itilcategories_id', '#0')";
                $DB->query($actionQuery);
            }

            // Location
            if ($target['location_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'locations_id', " . $target['location_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['location_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'locations_id', '#0')";
                $DB->query($actionQuery);
            }

            // Urgency
            if ($target['urgency_rule'] === 1) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'urgency', 3)";
                $DB->query($actionQuery);
            }
            if ($target['urgency_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'urgency', " . $target['urgency_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['urgency_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'urgency', '#0')";
                $DB->query($actionQuery);
            }

            // Type
            if ($target['type_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'type', " . $target['type_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['type_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'type', '#0')";
                $DB->query($actionQuery);
            }

            // Entity
            if ($target['destination_entity'] === 1) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_current', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_requester', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 5) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_form', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 6) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_validator', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 7) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entities_id', " . $target['destination_entity_value'] . ")"; // Todo to check
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 8) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_user', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 9) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'entities_id', '#0')";
                $DB->query($actionQuery);
            }

            //SLA & OLA
            if ($target['sla_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'slas_id_ttr', " . $target['sla_question_ttr'] . ")
                    , (" . $ruleId .", 'assign', 'slas_id_tto', " . $target['sla_question_tto'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['sla_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'slas_id_ttr', '#0')
                    , (" . $ruleId .", 'regex_result', 'slas_id_tto', '#0')";
                $DB->query($actionQuery);
            }
            if ($target['ola_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'olas_id_ttr', " . $target['ola_question_ttr'] . ")
                    , (" . $ruleId .", 'assign', 'olas_id_tto', " . $target['ola_question_tto'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['ola_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'olas_id_ttr', '#0')
                    , (" . $ruleId .", 'regex_result', 'olas_id_tto', '#0')";
                $DB->query($actionQuery);
            }

            // Actors
            $actorsQuery = "SELECT * FROM glpi_plugin_formcreator_targets_actors WHERE items_id=" . $target['id'];
            $actorsResult = $DB->query($actorsQuery);
            foreach ($actorsResult as $targetActor) {
                // Demandeur
                if ($targetActor['actor_role'] === 1 && $targetActor['actor_value']) {
                    if ($targetActor['actor_type'] === 1) { // Auteur du formulaire
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId . ", 'assign', '_groups_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
                // Observateur
                if ($targetActor['actor_role'] === 2) {
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 4) { // Personne depuis la question
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_groups_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 7) { // Fournisseur spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_suppliers_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
                // Attribué à
                if ($targetActor['actor_role'] === 3) {
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_groups_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 7) { // Fournisseur spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_suppliers_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
                // Todo finish actions
            }

            // CRITERIA : Add criteria to rule
            $conditionsQuery = "SELECT * FROM glpi_plugin_formcreator_conditions 
                WHERE itemtype='PluginFormcreatorTargetTicket' AND items_id=" . $target['id'];

            $conditionsResult = $DB->query($conditionsQuery);
            foreach ($conditionsResult as $condition) {
                $questionQuery = "SELECT name FROM glpi_plugin_formcreator_questions WHERE id=" .$condition['plugin_formcreator_questions_id'];
                $name = $DB->query($questionQuery)->fetch_assoc()['name'];

                $criteriaQuestionQuery = "INSERT INTO glpi_rulecriterias (rules_id, criteria, `condition`, pattern) 
                    VALUES (" . $ruleId .", 'question', 0, '" . $name . "')";
                $DB->query($criteriaQuestionQuery);

                if ($condition['show_condition'] === 1 || $condition['show_condition'] === 2 || $condition['show_condition'] === 8) {
                    $condition['show_condition']--;
                    $criteriaAnswerQuery = "INSERT INTO glpi_rulecriterias (rules_id, criteria, `condition`, pattern) 
                    VALUES (" . $ruleId .", 'answer', " . $condition['show_condition'] .", '" . $condition['show_value'] . "')";
                    $DB->query($criteriaAnswerQuery);
                }
            }
        }
    }

    private function upgradeChangeTargets() {
        global $DB;

        $query = 'SELECT * FROM  glpi_plugin_formcreator_targetchanges';
        $result = $DB->query($query);
        foreach ($result as $target) {
            $target['name'] = str_replace("'", "\'", $target['name']);
            $target['content'] = str_replace("'", "\'", $target['content']);

            // Add a rule pool
            $rulePoolQuery = "INSERT INTO glpi_plugin_formcreator_rulepools (name, plugin_formcreator_forms_id, comment) 
                VALUES ('". $target['name'] ."_pool', ". $target['plugin_formcreator_forms_id'] .", 'Migration de la cible ". $target['name'] ."')";
            $DB->query($rulePoolQuery);
            $rulePoolId = $DB->insertId();


            // Create a rule and link it to rulepool
            $ruleQuery = "INSERT INTO glpi_rules (entities_id, sub_type, name, is_active, description) 
                VALUES (0, 'PluginFormcreatorRule','". $target['name'] ."_rule', 1, 'Migration de la cible ". $target['name'] ."')";
            $DB->query($ruleQuery);
            $ruleId = $DB->insertId();

            $linkQuery = "INSERT INTO glpi_plugin_formcreator_rules (plugin_formcreator_forms_id, rules_pool_id, rules_id, rules_type) 
                VALUES (" . $target['plugin_formcreator_forms_id'] .", ". $rulePoolId .", " . $ruleId .", 'change')";
            $DB->query($linkQuery);


            // Add actions to rule
            // Title
            if ($target['target_name'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'name', '" . $target['target_name'] ."')";
                $DB->query($actionQuery);
            }

            // Content
            // Fullform
            if (strpos($target['content'], '##FULLFORM##') >= 0) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'fullform', 1)";
                $DB->query($actionQuery);
            } elseif ($target['content'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'name', '" . $target['content'] ."')";
                $DB->query($actionQuery);
            }

            // Category
            if ($target['category_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'itilcategories_id', " . $target['category_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['category_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'itilcategories_id', '#0')";
                $DB->query($actionQuery);
            }

//            // Location
//            if ($target['location_rule'] === 2) {
//                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
//                    VALUES (" . $ruleId .", 'assign', 'locations_id', " . $target['location_question'] . ")";
//                $DB->query($actionQuery);
//            }
//            if ($target['location_rule'] === 3) {
//                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
//                    VALUES (" . $ruleId .", 'regex_result', 'locations_id', '#0')";
//                $DB->query($actionQuery);
//            }

            // Urgency
            if ($target['urgency_rule'] === 1) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'urgency', 3)";
                $DB->query($actionQuery);
            }
            if ($target['urgency_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'urgency', " . $target['urgency_question'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['urgency_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'urgency', '#0')";
                $DB->query($actionQuery);
            }

//            // Type
//            if ($target['type_rule'] === 2) {
//                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
//                    VALUES (" . $ruleId .", 'assign', 'type', " . $target['type_question'] . ")";
//                $DB->query($actionQuery);
//            }
//            if ($target['type_rule'] === 3) {
//                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
//                    VALUES (" . $ruleId .", 'regex_result', 'type', '#0')";
//                $DB->query($actionQuery);
//            }

            // Entity
            if ($target['destination_entity'] === 1) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_current', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_requester', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 5) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_form', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 6) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_validator', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 7) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entities_id', " . $target['destination_entity_value'] . ")"; // Todo to check
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 8) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'entity_user', 1)";
                $DB->query($actionQuery);
            }
            if ($target['destination_entity'] === 9) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'entities_id', '#0')";
                $DB->query($actionQuery);
            }

            //SLA & OLA
            if ($target['sla_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'slas_id_ttr', " . $target['sla_question_ttr'] . ")
                    , (" . $ruleId .", 'assign', 'slas_id_tto', " . $target['sla_question_tto'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['sla_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'slas_id_ttr', '#0')
                    , (" . $ruleId .", 'regex_result', 'slas_id_tto', '#0')";
                $DB->query($actionQuery);
            }
            if ($target['ola_rule'] === 2) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'olas_id_ttr', " . $target['ola_question_ttr'] . ")
                    , (" . $ruleId .", 'assign', 'olas_id_tto', " . $target['ola_question_tto'] . ")";
                $DB->query($actionQuery);
            }
            if ($target['ola_rule'] === 3) {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'regex_result', 'olas_id_ttr', '#0')
                    , (" . $ruleId .", 'regex_result', 'olas_id_tto', '#0')";
                $DB->query($actionQuery);
            }

            //Plan & Analysis
            if ($target['controlistcontent'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'controlistcontent', '" . $target['controlistcontent'] ."')";
                $DB->query($actionQuery);
            }
            if ($target['impactcontent'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'impactcontent', '" . $target['impactcontent'] ."')";
                $DB->query($actionQuery);
            }
            if ($target['rolloutplancontent'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'rolloutplancontent', '" . $target['rolloutplancontent'] ."')";
                $DB->query($actionQuery);
            }
            if ($target['backoutplancontent'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'backoutplancontent', '" . $target['backoutplancontent'] ."')";
                $DB->query($actionQuery);
            }
            if ($target['checklistcontent'] !== '') {
                $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', 'checklistcontent', '" . $target['checklistcontent'] ."')";
                $DB->query($actionQuery);
            }

            //Actors
            $actorsQuery = "SELECT * FROM glpi_plugin_formcreator_targets_actors WHERE items_id=" . $target['id'];
            $actorsResult = $DB->query($actorsQuery);
            foreach ($actorsResult as $targetActor) {
                // Demandeur
                if ($targetActor['actor_role'] === 1) {
                    if ($targetActor['actor_type'] === 1) { // Auteur du formulaire
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId . ", 'assign', '_groups_id_requester', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
                // Observateur
                if ($targetActor['actor_role'] === 2) {
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 4) { // Personne depuis la question
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_groups_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 7) { // Fournisseur spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_suppliers_id_observer', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
                // Attribué à
                if ($targetActor['actor_role'] === 3) {
                    if ($targetActor['actor_type'] === 3) { // Personne spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_users_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 5) { // Groupe spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_groups_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                    if ($targetActor['actor_type'] === 7) { // Fournisseur spécifique
                        $actionQuery = "INSERT INTO glpi_ruleactions (rules_id, action_type, field, value)
                    VALUES (" . $ruleId .", 'assign', '_suppliers_id_assign', " . $targetActor['actor_value'] . ")";
                        $DB->query($actionQuery);
                    }
                }
            }

            // CRITERIA : Add criteria to rule
            $conditionsQuery = "SELECT * FROM glpi_plugin_formcreator_conditions 
                WHERE itemtype='PluginFormcreatorTargetChange' AND items_id=" . $target['id'];

            $conditionsResult = $DB->query($conditionsQuery);
            foreach ($conditionsResult as $condition) {
                $questionQuery = "SELECT name FROM glpi_plugin_formcreator_questions WHERE id=" .$condition['plugin_formcreator_questions_id'];
                $name = $DB->query($questionQuery)->fetch_assoc()['name'];

                $criteriaQuestionQuery = "INSERT INTO glpi_rulecriterias (rules_id, criteria, `condition`, pattern) 
                    VALUES (" . $ruleId .", 'question', 0, '" . $name . "')";
                $DB->query($criteriaQuestionQuery);

                if ($condition['show_condition'] === 1 || $condition['show_condition'] === 2 || $condition['show_condition'] === 8) {
                    $condition['show_condition']--;
                    $criteriaAnswerQuery = "INSERT INTO glpi_rulecriterias (rules_id, criteria, `condition`, pattern) 
                    VALUES (" . $ruleId .", 'answer', " . $condition['show_condition'] .", '" . $condition['show_value'] . "')";
                    $DB->query($criteriaAnswerQuery);
                }
            }
        }
    }
}