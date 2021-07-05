<?php
include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

$rulesPool = new PluginFormcreatorRulePool();
$rulesPool->showSubForm();