<?php

include ("../../../inc/includes.php");

Session::checkLoginUser();
Html::header(
    __('Rules pools', 'formcreator'),
    $_SERVER['PHP_SELF'],
    'admin',
    'PluginFormcreatorRulePool'
);

$rulepool = new PluginFormcreatorRulePool();

Search::show('PluginFormcreatorRulePool');

Html::footer();