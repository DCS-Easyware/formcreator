<?php

include ("../../../inc/includes.php");

Session::checkLoginUser();
Html::header(
    __('Rules', 'formcreator'),
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginFormcreatorRuleCollection'
); // Todo item : PluginFormcreatorRuleCollection not added to menu, so no breadcrumb...

if (isset($_GET['pool_id'])) {
    $_SESSION['plugin_formcreator_pool_id'] = $_GET['pool_id'];
}

$rulecollection = new PluginFormcreatorRuleCollection();

include (GLPI_ROOT . "/front/rule.common.php");