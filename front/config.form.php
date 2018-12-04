<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @author    David Durieux
 * @copyright
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkRight("config", UPDATE);
$pfcConfig = new PluginFormcreatorConfig();

if (!empty($_POST["update"])) {
   $config = new Config();
   $_POST['id'] = 1;
   $config->update($_POST);
   Html::back();
} else if (!empty($_POST['add_group'])) {
   $pfcConfig->addGroup($_POST);
   Html::back();
}

Html::header(
   PluginFormcreatorForm::getTypeName(2),
   $_SERVER['PHP_SELF'],
   'admin',
   'PluginFormcreatorForm',
   'config'
);

$pfcConfig->showForm();
Html::footer();
