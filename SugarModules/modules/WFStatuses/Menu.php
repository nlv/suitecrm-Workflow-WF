<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $mod_strings;
$module_menu = Array();

$module_menu[]=	Array("index.php?module=WFStatuses&action=EditView&return_module=WFStatuses&return_action=DetailView", $mod_strings['LBL_NEW_FORM_TITLE'],"CreateWFStatuses");

$module_menu[]=	Array("index.php?module=WFStatuses&action=index&return_module=WFStatuses&return_action=DetailView", $mod_strings['LBL_LIST_FORM_TITLE'],"WFStatuses");

global $current_language;
$events_mod_strings = return_module_language($current_language, 'WFEvents');
$module_menu[]=	Array("index.php?module=WFEvents&action=index&return_module=WFEvents&return_action=DetailView", $events_mod_strings['LBL_LIST_FORM_TITLE'],"WFEvents");

$workflows_mod_strings = return_module_language($current_language, 'WFWorkflows');
$module_menu[]=	Array("index.php?module=WFWorkflows&action=CheckWorkflows", $workflows_mod_strings['LBL_CHECK_WORKFLOWS'],"Workflows");
?>