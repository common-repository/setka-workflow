/* global jQuery, Backbone, module */
var
    adapter      = window.setkaWorkflowAdminGeneralPage,
    $            = jQuery,
    config       = window.setkaWorkflowAdminGeneralPageConfig,
    translations = window.setkaWorkflowAdminGeneralPageConfig.translations;

module.exports = Backbone.View.extend({

    disable: function() {
        this.$el.attr('disabled', 'disabled');
        return this;
    },

    enable: function() {
        this.$el.removeAttr('disabled');
        return this;
    }
});
