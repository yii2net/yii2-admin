/*!
 * Modal Remote
 * =================================
 * Use for xiongchuan86/yii2-kartikcrud extension
 */
(function ($) {
    $.fn.hasAttr = function (name) {
        return this.attr(name) !== undefined;
    };
}(jQuery));

/**
 *
 * @constructor
 * @see https://nakupanda.github.io/bootstrap3-dialog/
 */
function OpenadmModal() {

    this.defaults = {
        okLabel: "确定",
        executeLabel: "执行",
        cancelLabel: "取消",
        loadingTitle: "加载中..."
    };

    this.modal  = new BootstrapDialog({
        nl2br: false,
        draggable: true,
        type: BootstrapDialog.TYPE_PRIMARY
    });

    this.size = 'normal';
    this.size_fullscreen = 'fullscreen';
    this.size_normal = 'normal';

    this.modal.realize();
    this.modal.enableButtons(true);

    this.dialog = $(this.modal.getModalDialog());

    this.header = $(this.modal.getModalHeader());

    this.content = $(this.modal.getModalContent());

    this.footer = $(this.modal.getModalFooter());

    this.loadingContent = '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>';

    this.generateHeaderToolbar = function () {
        var toolbar = '<div class="bootstrap-dialog-close-button"><button class="close" data-dismiss="modal"><i class="fa fa-close"></i></button></div>' +
        '<div class="bootstrap-dialog-ext-button"><button class="expand" ><i class="fa  fa-square-o"></i></button><button class="compress" ><i class="fa fa-minus-square"></i></button></div>' +
        '';
        return toolbar;
    }

    this.addEventsForToolbar = function () {
        var exppand = $(this.header).find('.expand');
        var compress = $(this.header).find('.compress');
        var $this = this;
        exppand.bind('click',function () {
            $(this).css('display','none');
            $(compress).css('display','block');
            $this.dialog.addClass('modal-fullscreen');
        });
        compress.bind('click',function () {
            $(this).css('display','none');
            $(exppand).css('display','block');
            $this.dialog.removeClass('modal-fullscreen');
        });
    }

    $(this.header).find('.bootstrap-dialog-close-button').remove();
    $(this.header).find('.bootstrap-dialog-header').prepend(this.generateHeaderToolbar());


    /**
     * Show the modal
     */
    this.show = function () {
        this.clear();
        this.modal.open();
        this.addEventsForToolbar();
    };

    /**
     * Hide the modal
     */
    this.hide = function () {
        this.modal.close();
    };

    /**
     * Clear modal
     */
    this.clear = function () {
        this.modal.setTitle('');
        this.modal.setMessage('');
        $(this.footer).html("");
    };

    /**
     * Set size of modal
     * @param {string} size large/normal/small
     */
    this.setSize = function (size) {
        if(size == 'large'){
            size = BootstrapDialog.SIZE_LARGE;
        }else if(size == 'small'){
            size = BootstrapDialog.SIZE_SMALL;
        }else if(size == 'normal' || size == 'default' || size == ''){
            size = BootstrapDialog.SIZE_NORMAL;
        }else if(size == 'wide'){
            size = BootstrapDialog.SIZE_WIDE;
        }else {
            size = BootstrapDialog.SIZE_NORMAL;
        }
        this.modal.setSize(size);
    };

    /**
     * Set modal header
     * @param {string} content The content of modal header
     */
    this.setHeader = function (content) {
        $(this.header).html(content);
    };

    /**
     * Set modal content
     * @param {string} content The content of modal content
     */
    this.setContent = function (content) {
        this.modal.setMessage(content);
    };

    /**
     * Set modal footer
     * @param {string} content The content of modal footer
     */
    this.setFooter = function (content) {
        if(typeof content == 'string' && content){
            $(this.footer).css('display','block');
            $(this.footer).html(content);
        }else if(typeof content == 'object'){
            var buttons = '';
            for(var i in content){
                if(typeof content[i] == 'string'){
                    buttons += this.generateButton(content[i]);
                }
            }
            if(buttons){
                this.setFooter(buttons);
            }
        }
    };

    this.generateButton = function (type) {
        var button = '';
        switch (type) {
            case 'cancel':
                button = '<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="glyphicon glyphicon-ban-circle"></i> 关闭</button>';
                break;
            case 'ok':
                button = '<button type="button" class="btn btn-primary" data-dismiss="modal"><i class="glyphicon glyphicon-ok"></i> 确定</button>';
                break;
            case 'save':
                button = '<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> 保存</button>';
                break;
        }
        return button;
    }

    /**
     * Set modal footer
     * @param {string} title The title of modal
     */
    this.setTitle = function (title) {
        this.modal.setTitle(title);
    };

    /**
     * Hide close button
     */
    this.hidenCloseButton = function () {
        $(this.header).find('button.close').hide();
    };

    /**
     * Show close button
     */
    this.showCloseButton = function () {
        $(this.header).find('button.close').show();
    };

    /**
     * Show loading state in modal
     */
    this.displayLoading = function () {
        this.setContent(this.loadingContent);
        this.setTitle(this.defaults.loadingTitle);
    };

    /**
     * Add button to footer
     * @param string label The label of button
     * @param string classes The class of button
     * @param callable callback the callback when button click
     */
    this.addFooterButton = function (label, type, classes, callback) {
        buttonElm = document.createElement('button');
        buttonElm.setAttribute('type', type === null ? 'button' : type);
        buttonElm.setAttribute('class', classes === null ? 'btn btn-primary' : classes);
        buttonElm.innerHTML = label;
        var instance = this;
        $(this.footer).append(buttonElm);
        if (callback !== null) {
            $(buttonElm).click(function (event) {
                callback.call(instance, this, event);
            });
        }
    };

    /**
     * Send ajax request and wraper response to modal
     * @param {string} url The url of request
     * @param {string} method The method of request
     * @param {object}data of request
     */
    this.doRemote = function (url, method, data ,contentType) {
        var instance = this;
        $.ajax({
            url: url,
            method: method,
            data: data,
            async: false,
            beforeSend: function () {
                beforeRemoteRequest.call(instance);
            },
            error: function (response) {
                errorRemoteResponse.call(instance, response);
            },
            success: function (response) {
                successRemoteResponse.call(instance, response);
            },
            contentType: contentType?contentType:false,
            cache: false,
            processData: false
        });
    };

    /**
     * Before send request process
     * - Ensure clear and show modal
     * - Show loading state in modal
     */
    function beforeRemoteRequest() {
        this.show();
        this.displayLoading();
    }


    /**
     * When remote sends error response
     * @param {string} response
     */
    function errorRemoteResponse(response) {
        this.setTitle(response.status + response.statusText);
        this.setContent(response.responseText);
        this.addFooterButton('关闭', 'button', 'btn btn-default', function (button, event) {
            this.hide();
        })
    }

    /**
     * When remote sends success response
     * @param {string} response
     */
    function successRemoteResponse(response) {

        // Reload datatable if response contain forceReload field
        if (response.forceReload !== undefined && response.forceReload) {
            if (response.forceReload == 'true') {
                // Backwards compatible reload of fixed crud-datatable-pjax
                $.pjax.reload({container: '#crud-datatable-pjax'});
            } else {
                $.pjax.reload({container: response.forceReload});
            }
        }

        // Close modal if response contains forceClose field
        if (response.forceClose !== undefined && response.forceClose) {
            this.hide();
            return;
        }

        if (response.size !== undefined)
            this.setSize(response.size);

        if (response.title !== undefined)
            this.setTitle(response.title);

        if (response.content !== undefined)
            this.setContent(response.content);

        if (response.footer !== undefined)
            this.setFooter(response.footer);

        if ($(this.content).find("form")[0] !== undefined) {
            this.setupFormSubmit(
                $(this.content).find("form")[0],
                $(this.footer).find('[type="submit"]')[0]
            );
        }
    }

    /**
     * Prepare submit button when modal has form
     * @param {string} modalForm
     * @param {object} modalFormSubmitBtn
     */
    this.setupFormSubmit = function (modalForm, modalFormSubmitBtn) {

        if (modalFormSubmitBtn === undefined) {
            // If submit button not found throw warning message
            console.warn('模态框已定义，但是缺少提交按钮');
        } else {
            var instance = this;

            // Submit form when user clicks submit button
            $(modalFormSubmitBtn).click(function (e) {
                var data;

                // Test if browser supports FormData which handles uploads
                if (window.FormData) {
                    data = new FormData($(modalForm)[0]);
                } else {
                    // Fallback to serialize
                    data = $(modalForm).serializeArray();
                }

                instance.doRemote(
                    $(modalForm).attr('action'),
                    $(modalForm).hasAttr('method') ? $(modalForm).attr('method') : 'GET',
                    data
                );
            });
        }
    };

    /**
     * Show the confirm dialog
     * @param {string} title The title of modal
     * @param {string} message The message for ask user
     * @param {string} okLabel The label of ok button
     * @param {string} cancelLabel The class of cancel button
     * @param {string} size The size of the modal
     * @param {string} dataUrl Where to post
     * @param {string} dataRequestMethod POST or GET
     * @param {number[]} selectedIds
     */
    this.confirmModal = function (title, message, okLabel, cancelLabel, size, dataUrl, dataRequestMethod, selectedIds) {
        var instance = this;
        window.BootstrapDialog.show({
            type :BootstrapDialog.TYPE_DANGER,
            title: title,
            message: '<form id="ModalRemoteConfirmForm">'+message,
            buttons: [
                {
                    label: '<i class="glyphicon glyphicon-ban-circle"></i> 取消',
                    cssClass: 'btn btn-default',
                    action: function(dialog) {
                        dialog.close();
                    }
                },
                {
                    label: '<i class="glyphicon glyphicon-ok"></i> 确定',
                    cssClass: 'btn btn-danger',
                    action: function(dialog) {
                        dialog.close();
                        var data;
                        if (window.FormData) {
                            data = new FormData($('#ModalRemoteConfirmForm')[0]);
                            if (typeof selectedIds !== 'undefined' && selectedIds)
                                data.append('pks', selectedIds.join());
                        } else {
                            // Fallback to serialize
                            data = $('#ModalRemoteConfirmForm');
                            if (typeof selectedIds !== 'undefined' && selectedIds)
                                data.pks = selectedIds;
                            data = data.serializeArray();
                        }

                        instance.doRemote(
                            dataUrl,
                            dataRequestMethod,
                            data
                        );
                    }
                },
            ]
        });

    }

    /**
     * Open the modal
     * HTML data attributes for use in local confirm
     *   - href/data-url         (If href not set will get data-url)
     *   - data-request-method   (string GET/POST)
     * Attributes for remote response (json)
     *   - forceReload           (string reloads a pjax ID)
     *   - forceClose            (boolean remote close modal)
     *   - size                  (string small/normal/large)
     *   - title                 (string/html title of modal box)
     *   - content               (string/html content in modal box)
     *   - footer                (string/html footer of modal box)
     * @params {elm}
     */
    this.open = function (elm, bulkData) {
        if ($(elm).hasAttr('data-confirm-title') || $(elm).hasAttr('data-confirm-message')) {
            this.confirmModal (
                $(elm).attr('data-confirm-title'),
                $(elm).attr('data-confirm-message'),
                $(elm).attr('data-confirm-ok'),
                $(elm).attr('data-confirm-cancel'),
                $(elm).hasAttr('data-modal-size') ? $(elm).attr('data-modal-size') : 'normal',
                $(elm).hasAttr('href') ? $(elm).attr('href') : $(elm).attr('data-url'),
                $(elm).hasAttr('data-request-method') ? $(elm).attr('data-request-method') : 'GET',
                bulkData
            )
        } else {
            this.doRemote(
                $(elm).hasAttr('href') ? $(elm).attr('href') : $(elm).attr('data-url'),
                $(elm).hasAttr('data-request-method') ? $(elm).attr('data-request-method') : 'GET',
                bulkData
            );
        }
    }
} // End of Object
