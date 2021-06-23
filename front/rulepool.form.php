<?php

include ("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

Html::header(
    PluginFormcreatorRulePool::getTypeName(2),
    $_SERVER['PHP_SELF'],
    "admin",
    'PluginFormcreatorRulePool'
);

$plugin = new Plugin();
if ($plugin->isActivated("formcreator")) {
    $rulepool = new PluginFormcreatorRulePool();

    if (isset($_POST["add"])) {
        Toolbox::logError($_POST);
        $id = $rulepool->add($_POST);
        Html::back();
    } else if (isset($_POST["update"])) {
        $rulepool->update($_POST);
        Html::back();
    } else if (isset($_POST["delete_rule"])) {
//        $rulepool->cleanRules($_POST);
        if ($rulepool->delete($_POST)) {
            if ($rulepool->cleanRules($_POST)) {
                Html::redirect($CFG_GLPI["root_doc"] . '/plugins/formcreator/front/rulepool.php');
            } else {
                Html::back(); // Todo display error
            }
        }
    } else {
        $id = $_GET["id"] ?? '';
        $rulepool->display([
            'id'        => $id,
            'itemtype'  => $rulepool->itemtype
        ]);
        $_SESSION['plugin_formcreator_pool_id'] = $id;

        Html::footer();
    }
} else {
    Html::displayNotFoundError();
}
