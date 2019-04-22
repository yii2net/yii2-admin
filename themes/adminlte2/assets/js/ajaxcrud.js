/*!
 * Ajax Crud 
 * =================================
 * Use for openadm/yii2-kartikcrud extension
 */
$(document).ready(function () {
    // Catch click event on all buttons that want to open a modal
    $(document).on('click', '[role="modal-remote"]', function (event) {
        event.preventDefault();
        var openadmModal = null
        var reusemodal = $(this).data('reusemodal') || 0;
        if(reusemodal == 1){
            openadmModal = $(this).data('openadmModal') || null;
        }
        if(openadmModal == null){
            openadmModal = new OpenadmModal();
        }
        openadmModal.open(this, null);
    });

    // Catch click event on all buttons that want to open a modal
    // with bulk action
    $(document).on('click', '[role="modal-remote-bulk"]', function (event) {
        event.preventDefault();

        // Collect all selected ID's
        var selectedIds = [];
        $('input:checkbox[name="selection[]"]').each(function () {
            if (this.checked){
                selectedIds.push($(this).val());
            }

        });

        if (selectedIds.length == 0) {
            oa.Noty({type:'warning',text:"请至少选择一条数据！"});
        } else {
            // Open modal
            var openadmModal = new OpenadmModal();
            openadmModal.open(this, selectedIds);
        }
    });
});