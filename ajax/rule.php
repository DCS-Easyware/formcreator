<?php

include ('../../../inc/includes.php');
Session::checkRight("entity", UPDATE);

$rule = new PluginFormcreatorRule();
$rule->showSubForm();