<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class WFModule extends SugarBean {

	var $id;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $name;
	
	var $wf_module;
	var $type_field;
	
	var $table_name = "wf_modules";
	var $object_name = "WFModule";
	var $module_dir = 'WFModules';
	var $importable = true;

	function WFModule() {
		parent::SugarBean();
	}

	var $new_schema = true;

	function get_summary_text()
	{
		return $this->wf_module;
	}

	function ACLAccess($view, $is_owner='not_set', $in_group = 'not_set')
	{
		return $GLOBALS['current_user']->isAdmin();
	}

	function getUtilityVardefsFileName()
	{
		return "custom/Extension/modules/{$this->wf_module}/Ext/Vardefs/wf_module.php";
	}

	function createUtilityVardefsFile()
	{
		$utility_fields_file_name = $this->getUtilityVardefsFileName();
		if (!file_exists($utility_fields_file_name)) {
			$objectName = BeanFactory::newBean($this->wf_module)->object_name;
			if (empty($objectName)) {
				throw new Exception("Can't find object name for module " . $this->wf_module);
			}
			mkdir_recursive(dirname($utility_fields_file_name));
			$code = <<<PHP
<?php
// Generated by WFModule

/**
 * Workflow id
 */
\$dictionary['{$objectName}']['fields']['wf_id'] = array(
    'name' => 'wf_id',
    'vname' => 'LBL_WF_ID',
    'type' => 'id',
    'audited' => true,
);

/**
 * Status audit non-db field
 */
\$dictionary['{$objectName}']['fields']['wf_status_audit'] = array(
    'name' => 'wf_status_audit',
    'vname' => 'LBL_CONFIRM_LIST',
    'type' => 'WFStatusAudit',
    'source' => 'non-db',
    'inline_edit' => false,
);

/**
 * Status audit text field
 */
\$dictionary['{$objectName}']['fields']['confirm_list'] = array(
    'name' => 'confirm_list',
    'type' => 'text',
    'vname' => 'LBL_CONFIRM_LIST',
    'audited' => false,
    'massupdate' => false,
    'inline_edit' => false,
    'importable' => 'false',
);

/**
 * Current resolution non-db field
 */
\$dictionary['{$objectName}']['fields']['last_resolution'] = array(
    'name' => 'last_resolution',
    'type' => 'text',
    'source' => 'non-db',
    'audited' => false,
    'massupdate' => false,
);

/**
 * Confirm block
 */
\$dictionary['{$objectName}']['fields']['wf_confirm_block'] = array(
    'name' => 'wf_confirm_block',
    'vname' => 'LBL_CONFIRM_STATUS',
    'type' => 'text',
    'source' => 'non-db',
    'audited' => false,
    'massupdate' => false,
    'inline_edit' => false,
    'importable' => 'false',
    'function' => array(
        'include' => 'custom/include/Workflow/utils.php',
        'name' => 'wf_confirmBlock',
        'returns' => 'html',
    ),
    'studio' => true,
);

/**
 * Assign block
 */
\$dictionary['{$objectName}']['fields']['wf_assign_block'] = array(
    'name' => 'wf_assign_block',
    'vname' => 'LBL_ASSIGNED_CHANGE_TITLE',
    'type' => 'text',
    'source' => 'non-db',
    'audited' => false,
    'massupdate' => false,
    'inline_edit' => false,
    'importable' => 'false',
    'function' => array(
        'include' => 'custom/include/Workflow/utils.php',
        'name' => 'wf_assignBlock',
        'returns' => 'html',
    ),
    'studio' => true,
);

/**
 * Assigned users block
 */
\$dictionary['{$objectName}']['fields']['wf_assigned_block'] = array(
    'name' => 'wf_assigned_block',
    'vname' => 'LBL_ASSIGNEES',
    'type' => 'text',
    'source' => 'non-db',
    'audited' => false,
    'massupdate' => false,
    'inline_edit' => false,
    'importable' => 'false',
    'function' => array(
        'include' => 'custom/include/Workflow/utils.php',
        'name' => 'wf_assigneesBlock',
        'returns' => 'html',
    ),
    'studio' => true,
);

PHP;
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

	function getLogicHooks()
	{
		return array(
			array (
				'module' => $this->wf_module,
				'hook' => 'before_save',
				'order' => 99,
				'description' => 'WFModule: Check before save record',
				'file' => 'custom/include/Workflow/WF_hooks.php',
				'class' => 'WF_hooks',
				'function' => 'before_save',
			),
			array (
				'module' => $this->wf_module,
				'hook' => 'after_save',
				'order' => 99,
				'description' => 'WFModule: Run after save function',
				'file' => 'custom/include/Workflow/WF_hooks.php',
				'class' => 'WF_hooks',
				'function' => 'after_save',
			),
		);
	}

	function createLogicHooks()
	{
		foreach($this->getLogicHooks() as $hook ) {
			check_logic_hook_file($hook['module'], $hook['hook'], array($hook['order'], $hook['description'],  $hook['file'], $hook['class'], $hook['function']));
		}
	}

	function removeLogicHooks()
	{
		foreach($this->getLogicHooks() as $hook ) {
			remove_logic_hook($hook['module'], $hook['hook'], array($hook['order'], $hook['description'],  $hook['file'], $hook['class'], $hook['function']));
		}
	}
}

