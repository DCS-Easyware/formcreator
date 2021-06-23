<?php

include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
    $rule = new PluginFormcreatorRule();

    if (isset($_POST["add"]) && !empty($_POST['plugin_formcreator_forms_id'])) {
        $id = $rule->add($_POST);
        $rule->linkTable($id, $_POST['plugin_formcreator_forms_id'], $_POST['type']);
        Html::back();
    } else if (isset($_POST["update"])) {
        $rule->update($_POST);
        Html::back();
    } else if (isset($_POST["delete_rule"])) {
        if ($rule->delete($_POST)) {
            $rule->removeFromLinkTable($_POST['id']);
            Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/form.form.php?id=' . $_POST['plugin_formcreator_forms_id']);
        }
    } else if (isset($_GET['id'])) {
        $rulecollection = new PluginFormcreatorRuleCollection();

        include (GLPI_ROOT . "/front/rule.common.form.php");
    } else {
        Html::back();
    }

    // Or display a "Not found" error
} else {
    Html::displayNotFoundError();
}
