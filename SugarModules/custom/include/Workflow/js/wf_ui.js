if(!lab321) var lab321 = {};
if(!lab321.wf) lab321.wf = {};

if(!lab321.getSugarListViewCheckedRecords) {
    lab321.getSugarListViewCheckedRecords = function() {
        var mode = document.MassUpdate.select_entire_list && document.MassUpdate.select_entire_list.value == 1 ? 'entire' : 'selected';
        var ar = [];
        if(mode != 'entire') {
            $('#MassUpdate input[name="mass[]"]:checked').each(function(){
                ar.push($(this).val());
            });
        }
        return {
            mode: mode,
            items: ar
        };
    };
}

lab321.wf.setStatusOptions = function(statuses, formName) {
    var html = '';
    var selectedStatus = $('#'+formName+' #newStatus').val();
    for(var key in statuses) {
        if(typeof statuses[key] == 'string') {
            html += '<option value="'+key+'"'+(selectedStatus == key ? ' selected="selected"' : '')+'>'+statuses[key]+'</option>';
        }
    }
    $('#'+formName+' #newStatus').html(html);
};

lab321.wf.confirmStatus = function(formName) {
    if(!lab321.wf.confirmRequest) {
        lab321.wf.confirmRequest = {};
    }
    if(lab321.wf.confirmRequest.status == 'sent') {
        return;
    }
    if(!check_form(formName)) {
        return;
    }
    ajaxStatus.showStatus(SUGAR.language.get('app_strings','LBL_SAVING'));
    lab321.wf.confirmRequest.status = 'sent';
    lab321.wf.confirmRequest.saveButtonOnclickValue = $('#'+formName+' input[type="submit"]').attr('onclick');
    $('#'+formName+' input[type="submit"]').attr('onclick', 'return false');

    $.ajax('index.php?entryPoint=wf_confirm', {
        data: {
            record: $('#'+formName+' #record').val(),
            module: $('#'+formName+' #module').val(),
            status: $('#'+formName+' select[name="status"]').val(),
            assigned_user: $('#'+formName+' select[name="assigned_user"]').val(),
            resolution: $('#'+formName+' #resolution').val(),
            is_ajax_call: 1,
        },
        type: 'POST',
        dataType: 'json'
    }).done(function(data) {
        lab321.wf.setConfirmErrors(data.errors, formName);
        ajaxStatus.hideStatus();
        if(data.saved) {
            ajaxStatus.flashStatus(SUGAR.language.get('app_strings','LBL_SAVED'), 3000);
            location.reload();
        }
    }).fail(function() {
        ajaxStatus.hideStatus();
    }).always(function() {
        $('#'+formName+' input[type="submit"]').attr('onclick', lab321.wf.confirmRequest.saveButtonOnclickValue || '');
        lab321.wf.confirmRequest.status = 'done';
    });
}

lab321.wf.massConfirmSave = function(formName) {
    lab321.wf.massConfirm('save', formName);
};

lab321.wf.massConfirm = function(action, formName) {
    if(!lab321.wf.massConfirmRequest) {
        lab321.wf.massConfirmRequest = {};
    }
    if(lab321.wf.massConfirmRequest.status == 'resend') {
        return;
    }
    if(lab321.wf.massConfirmRequest.status == 'sent') {
        lab321.wf.massConfirmRequest.status = 'resend';
        lab321.wf.massConfirmRequest.resendAction = action;
        return;
    }
    if(action == 'save') {
        ajaxStatus.showStatus(SUGAR.language.get('app_strings','LBL_SAVING'));
    }
    lab321.wf.massConfirmRequest.status = 'sent';
    lab321.wf.massConfirmRequest.bSubmitAfterCheck = false;
    lab321.wf.massConfirmRequest.saveButtonOnclickValue = $('#'+formName+' input[type="submit"]').attr('onclick');
    
    var checkedRecords = lab321.getSugarListViewCheckedRecords();
    $('#'+formName+' input[type="submit"]').attr('onclick', 'lab321.wf.massConfirmRequest.bSubmitAfterCheck = true; return false');

    var module = $('#MassUpdate input[name="module"]').val();
    
    $.ajax('index.php?entryPoint=wf_mass_confirm', {
        data: {
            action: action,
            checkedRecords: checkedRecords,
            module: module,
            status: $('#'+formName+' select[name="status"]').val(),
            assigned_user: $('#'+formName+' select[name="assigned_user"]').val(),
            resolution: action == 'save' ? $('#'+formName+' #resolution').val() : '',
        },
        type: 'POST',
        dataType: 'json'
    }).done(function(data) {
        lab321.wf.setConfirmErrors(data.errors, formName);
        $('#'+formName+' #current_status').val(data.editFormData.currentStatus);
        if(data.editFormData) {
            $('#'+formName).data('assignedUsers', data.editFormData.assignedUsers || []);
            lab321.wf.setStatusOptions((data.editFormData.confirmData || {}).newStatuses || [], formName);
        }
        lab321.wf.onChangeNewStatus();
        ajaxStatus.hideStatus();
        if(data.saved) {
            ajaxStatus.flashStatus(SUGAR.language.get('app_strings','LBL_SAVED'), 3000);
            $('#'+formName+' #resolution').val('');
        }
    }).fail(function() {
        ajaxStatus.hideStatus();
    }).always(function() {
        $('#'+formName+' input[type="submit"]').attr('onclick', lab321.wf.massConfirmRequest.saveButtonOnclickValue || '');
        if(lab321.wf.massConfirmRequest.bSubmitAfterCheck) {
            $('#'+formName+' input[type="submit"]').click();
        }
        lab321.wf.massConfirmRequest.bSubmitAfterCheck = false;
        var status = lab321.wf.massConfirmRequest.status;
        lab321.wf.massConfirmRequest.status = 'done';
        if(status == 'resend') {
            lab321.wf.massConfirm(lab321.wf.massConfirmRequest.resendAction, formName);
        }
    });
};

lab321.wf.setListViewHandlers = function() {
    var origCheckItem = sugarListView.prototype.check_item;
    sugarListView.prototype.check_item = function(cb, form) {
        origCheckItem(cb, form);
        updateAvailableStatuses();
    };
    var origClearAll = sugarListView.prototype.clear_all;
    sugarListView.prototype.clear_all = function() {
        origClearAll();
        updateAvailableStatuses();
    };
    var origCheckAll = sugarListView.prototype.check_all;
    sugarListView.prototype.check_all = function(form, field, value, pageTotal) {
        origCheckAll(form, field, value, pageTotal);
        updateAvailableStatuses();
    };
    var origCheckEntire = sugarListView.prototype.check_entire_list;
    sugarListView.prototype.check_entire_list = function(form, field, value, list_count) {
        origCheckEntire(form, field, value, list_count);
        updateAvailableStatuses();
    };
    
    function updateAvailableStatuses() {
        if($('#confirm_block').is(':visible') && $('#confirm_panel').is(':visible')) {
            lab321.wf.massConfirm('check');
        }
        else {
            if(!lab321.wf.massConfirmRequest) {
                lab321.wf.massConfirmRequest = {};
            }
            if(lab321.wf.massConfirmRequest.status != 'sent') {
                lab321.wf.massConfirmRequest.status = 'delay';
            }
        }
    }
};

lab321.wf.setConfirmErrors = function(errors, formName) {
    var html = '';
    html = '<ul>';
    if(errors.length > 0) {
        var title = SUGAR.language.get('app_strings','LBL_CONFIRM_ERRORS_TITLE');
        html += '<li>'+(title != 'undefined' ? title : '')+'</li>';
    }
    for(var i in errors) {
        if(typeof errors[i] == 'string') { //jit.js добавляет Array.prototype.sum
            html += '<li>'+errors[i]+'</li>';
        }
    }
    html += '</ul>';
    $('#'+formName+' .errors').html(html);
};

lab321.wf.togglePanel = function() {
    var id = 'confirm_panel';
    var panel = document.getElementById(id);
    if (panel.style.display == 'none') {
        panel.style.display = 'block';
        document.getElementById(id + "_toggle_img").src = 'themes/default/images/basic_search.gif';
        if ((lab321.wf.massConfirmRequest || {}).status == 'delay') {
            lab321.wf.massConfirm('check');
        }
    } else {
        panel.style.display = 'none';
        document.getElementById(id + "_toggle_img").src = 'themes/default/images/advanced_search.gif';
    }
}

lab321.wf.onChangeNewStatus = function(formName) {
    var statusSel = $('#'+formName+' #newStatus').get(0);
    if (!statusSel)
        return;
    var disable = true;
    var userSel = document[formName].assigned_user;
    userSel.options.length = 0;
    if (statusSel.length > 0) {
        var status = statusSel[statusSel.selectedIndex].value;
        var assignedUsers = $('#'+formName).data('assignedusers');
        if (status != "" && assignedUsers[status] !== undefined && assignedUsers[status].length > 0) {
            disable = false;

            for (i = 0; i < assignedUsers[status].length; i++)
                userSel.options[i] = new Option(assignedUsers[status][i][1], assignedUsers[status][i][0]);
        }
    }
    document[formName].submit_btn.disabled = disable;
}

lab321.wf.onChangeRole = function(formName) {
    var masterSel = $('#'+formName+' #role').get(0);
    if (!masterSel)
        return;
    if (masterSel.length > 0) {
        var status = masterSel[masterSel.selectedIndex].value;
        var userSel = document[formName].new_assign_user;
        var confirmUsers = $('#'+formName).data('confirmusers');
        userSel.options.length = 0;
        if (status != "" && confirmUsers[status] !== undefined && confirmUsers[status].length > 0) {
            for (i = 0; i < confirmUsers[status].length; i++)
                userSel.options[i] = new Option(confirmUsers[status][i][1], confirmUsers[status][i][0]);
        }
    }
}
