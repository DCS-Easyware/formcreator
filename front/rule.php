<?php

include ("../../../inc/includes.php");

Session::checkLoginUser();
Html::header(
    __('Form Creator', 'formcreator'),
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginFormcreatorRuleCollection'
);

$rulecollection = new PluginFormcreatorRuleCollection();

include (GLPI_ROOT . "/front/rule.common.php");