<script>
{literal}
SUGAR.util.doWhen('document.readyState == "complete" && typeof lab321 != "undefined" && typeof lab321.wf != "undefined"', function() {
{/literal}
    lab321.wf.confirmUsers = {$workflow.confirmUsersString};
{literal}
    lab321.wf.onChangeRole();
});
{/literal}
</script>

<div id="assign_block">
  <h4>{sugar_translate label='LBL_ASSIGNED_CHANGE_TITLE' module='WFWorkflows'}</h4>
  <form id='assign' name='assign' action='index.php?entryPoint=wf_assign' method='POST'
         style="margin-top: 5px;
                border-style: solid;
                border-width: 1px;
                border-color: #abc3d7;
                padding: 5px;
                padding-right: 0px;">
    <input type='hidden' id='record' name='record' value='{$fields.id.value}'> 
    <input type='hidden' id='module' name='module' value='{$module}'>

    <input type='hidden' id='return_module' name='return_module' value = '{$return_module}'>
    <input type='hidden' id='return_action' name='return_action' value = '{$return_action}'>
    <input type='hidden' id='return_record' name='return_record' value = '{$return_record}'>

    <table border="0" margin="5" style="min-width:400px">
      <tr margin="15">
        <td style="padding:5px"><label for="status">{sugar_translate label='LBL_ROLE' module='WFWorkflows'}:</label><span class="required">*</span></td>
        <td style="padding:5px">{html_options name=role options=$workflow.roles id=role selected=$workflow.currentRole style="width:100%"
                                             onchange="lab321.wf.onChangeRole();"}</td> 
      </tr>
      <tr margin="15">
        <td style="padding:5px"><label for="status">{sugar_translate label='LBL_NEW_ASSIGNED' module='WFWorkflows'}:</label><span class="required">*</span></td>
        <td style="padding:5px">{html_options name=new_assign_user options="" id=new_assign_user style="width:100%"}</td> 
      </tr>
      <tr margin="15">
        <td style="padding:5px"></td>
        <td style="padding:5px"><input type='submit' name='submit_btn' value='{sugar_translate label='LBL_ASSIGN_SUBMIT' module='WFWorkflows'}'></td>
      </tr>
    </table>
  </form>
</div>