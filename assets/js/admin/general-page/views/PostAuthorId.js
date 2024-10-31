/* global jQuery, Backbone, module */
var
    adapter      = window.setkaWorkflowAdminGeneralPage,
    $            = jQuery,
    config       = window.setkaWorkflowAdminGeneralPageConfig,
    translations = window.setkaWorkflowAdminGeneralPageConfig.translations;

module.exports = Backbone.View.extend({

    el: config.elements.postAuthorId,

    initialize: function() {
        this.$el.select2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                minimumInputLength: 3,
                delay: 300,
                allowClear: true,
                data: function (params) {
                    params.action = config.action;
                    params.actionName = config.actionSearchUsers;
                    return params;
                }
            }
        });
    }
});
