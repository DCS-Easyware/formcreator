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
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
Session::checkRight('entity', UPDATE);

if (!isset($_REQUEST['questionId'])) {
   http_response_code(400);
   exit();
}
if (!isset($_REQUEST['questionType'])) {
   http_response_code(400);
   exit();
}

$question = new PluginFormcreatorQuestion();
$question->getEmpty();
if (!$question->isNewID((int) $_REQUEST['questionId']) && !$question->getFromDB((int) $_REQUEST['questionId'])) {
   http_response_code(400);
   exit();
}

$question->fields['fieldtype'] = $_REQUEST['questionType'];
$field = PluginFormcreatorFields::getFieldInstance(
   $question->fields['fieldtype'],
   $question
);
$json = [
   'label' => '',
   'field' => '',
   'additions' => '',
   'may_be_empty' => false,
];
if ($field !== null) {
   $field->deserializeValue($question->fields['default_values']);
   $json = $field->getDesignSpecializationField();

   // Limit subset of dropdown  and glpiselect
   preg_match_all('<optgroup (.*?)/optgroup>', $json['field'], $matches, PREG_SET_ORDER);
   foreach ($matches as $index => $group) {
      // Dropdown => Intitulé
      if ('dropdown' === $question->fields['fieldtype']) {
         if (!str_contains($group[0], 'label="G&eacute;n&eacute;ral"') && !str_contains($group[0], 'label="Assistance"')) {
            $json['field'] = str_replace($group[0], '<optgroup></optgroup>', $json['field']);
         }

         // Général : Lieux
         if (str_contains($group[0], 'label="G&eacute;n&eacute;ral"')) {
            $replacement = '';
            preg_match_all('<option (.*?)/option>', $group[0], $subMatches, PREG_SET_ORDER);
            foreach ($subMatches as $idx => $grp) {
               if (!str_contains($grp[0], 'Lieux')) {
                  $replacement .= '<option></option>';
               } else {
                  $replacement .= $grp[0];
               }
            }
            if (count($subMatches) > 0) {
               $json['field'] = str_replace($group[0], $replacement, $json['field']);
            }
         }

         // Assistance : Catégories ITIL
         if (str_contains($group[0], 'label="Assistance"')) {
            $replacement = '';
            preg_match_all('<option (.*?)/option>', $group[0], $subMatches, PREG_SET_ORDER);
            foreach ($subMatches as $idx => $grp) {
               if (!str_contains($grp[0], 'Cat&eacute;gories ITIL')) {
                  $replacement .= '<option></option>';
               } else {
                  $replacement .= $grp[0];
               }
            }
            if (count($subMatches) > 0) {
               $json['field'] = str_replace($group[0], $replacement, $json['field']);
            }
         }
      }

      // Glpiselect => Objet GLPI
      if ('glpiselect' === $question->fields['fieldtype']) {
         if (!str_contains($group[0], 'label="Parc"') && !str_contains($group[0], 'label="Administration"')) {
            $json['field'] = str_replace($group[0], '<optgroup></optgroup>', $json['field']);
         }

         // Parc : Ordinateurs
         if (str_contains($group[0], 'label="Parc"')) {
            $replacement = '';
            preg_match_all('<option (.*?)/option>', $group[0], $subMatches, PREG_SET_ORDER);
            foreach ($subMatches as $idx => $grp) {
               if (!str_contains($grp[0], 'Ordinateurs')) {
                  $replacement .= '<option></option>';
               } else {
                  $replacement .= $grp[0];
               }
            }
            if (count($subMatches) > 0) {
               $json['field'] = str_replace($group[0], $replacement, $json['field']);
            }
         }

         // Administration : Entités, Groupes & Utilisateurs
         if (str_contains($group[0], 'label="Administration"')) {
            $replacement = '';
            preg_match_all('<option (.*?)/option>', $group[0], $subMatches, PREG_SET_ORDER);
            foreach ($subMatches as $idx => $grp) {
               if (!str_contains($grp[0], 'Groupes') && !str_contains($grp[0], 'Utilisateurs') && !str_contains($grp[0], 'Entit&eacute;s')) {
                  $replacement .= '<option></option>';
               } else {
                  $replacement .= '<'. $grp[0] . '</option>';
               }
            }
            if (count($subMatches) > 0) {
               $json['field'] = str_replace($group[0], $replacement, $json['field']);
            }
         }
      }
   }
}
echo json_encode($json);
