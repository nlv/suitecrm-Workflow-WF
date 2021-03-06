<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class WFWorkflow extends SugarBean {

	var $id;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $name;
	var $wf_module;
	var $status_field;
	var $bean_type;

	var $table_name = "wf_workflows";
	var $object_name = "WFWorkflow";
	var $module_dir = 'WFWorkflows';
	var $importable = true;

	function WFWorkflow() {
		parent::SugarBean();
	}

	var $new_schema = true;

	function get_summary_text()
	{
		return "{$this->wf_module} / {$this->name}";
	}

	function ACLAccess($view, $is_owner='not_set', $in_group = 'not_set')
	{
		return $GLOBALS['current_user']->isAdmin();
	}

	function getUtilityVardefsFileName()
	{
		return "custom/Extension/modules/{$this->wf_module}/Ext/Vardefs/wf_workflow.php";
	}

	function getOptionsName()
	{
		$object = BeanFactory::newBean($this->wf_module);
		$objectName = $object->object_name;
		if (empty($objectName)) {
			throw new Exception("Can't find object name for module " . $this->wf_module);
		}
		return !empty($object->field_defs[$this->status_field]['options'])
				&& is_string($object->field_defs[$this->status_field]['options'])
			? $object->field_defs[$this->status_field]['options']
			: "{$this->wf_module}_{$this->status_field}_wf_options";
	}
	function createUtilityVardefsFile()
	{
		$utility_fields_file_name = $this->getUtilityVardefsFileName();
		if (!file_exists($utility_fields_file_name)) {
			$object = BeanFactory::newBean($this->wf_module);
			$objectName = $object->object_name;
			if (empty($objectName)) {
				throw new Exception("Can't find object name for module " . $this->wf_module);
			}
			$optionsName = $this->getOptionsName();
			mkdir_recursive(dirname($utility_fields_file_name));
			$code = <<<PHP
<?php
// Generated by WFWorkflow

/**
 * Status field settings
 */
\$dictionary['{$objectName}']['fields']['{$this->status_field}']['default'] = '';
\$dictionary['{$objectName}']['fields']['{$this->status_field}']['required'] = false;
\$dictionary['{$objectName}']['fields']['{$this->status_field}']['options'] = '$optionsName';

PHP;
// \$dictionary['{$objectName}']['fields']['{$this->status_field}']['function'] = 'wf_getNewStatuses';
// TODO: support 'function' in ListView. Currently ListView does not translate fields with non empty 'function'.
			$d = file_put_contents($utility_fields_file_name, $code);
			if ($d === false) {
				throw new Exception("Can't create file " . $utility_fields_file_name);
			}
		}
	}

	function removeUtilityVardefsFile()
	{
		$utility_fields_file_name = $this->getUtilityVardefsFileName();
		if (file_exists($utility_fields_file_name)) {
			unlink($utility_fields_file_name);
		}
	}

	function getLangVardefsFileName($lang)
	{
		return "custom/Extension/application/Ext/Language/{$lang}.{$this->wf_module}_{$this->status_field}_wf_options.php";
	}

	function createLangVardefsFile($lang)
	{
		$utility_fields_file_name = $this->getLangVardefsFileName($lang);
		if (!file_exists($utility_fields_file_name)) {
			mkdir_recursive(dirname($utility_fields_file_name));
			$optionsName = $this->getOptionsName();
			$code = <<<PHP
<?php
// Generated by WFWorkflow

if (file_exists('custom/include/Workflow/WFManager.php')) {
    require_once 'custom/include/Workflow/WFManager.php';
    \$app_list_strings['$optionsName'] =
        WFManager::getAllStatuses(BeanFactory::newBean('{$this->wf_module}'));
}

PHP;
			$d = file_put_contents($utility_fields_file_name, $code);
			if ($d === false) {
				throw new Exception("Can't create file " . $utility_fields_file_name);
			}
		}
	}

	function removeLangVardefsFile($lang)
	{
		$utility_fields_file_name = $this->getLangVardefsFileName($lang);
		if (file_exists($utility_fields_file_name)) {
			unlink($utility_fields_file_name);
		}
	}
}

require_once ("custom/include/Workflow/utils.php");
