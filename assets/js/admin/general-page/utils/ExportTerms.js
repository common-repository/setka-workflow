/* global jQuery, Backbone, module */
var
    $       = jQuery,
    config  = window.setkaWorkflowAdminGeneralPageConfig;

module.exports = Backbone.View.extend({

    _waitResponse: false,

    initialize: function() {
        _.bindAll(this, 'done', 'fail');
    },

    exportTerm: function() {

        // Don'make request if previous not finished.
        if(this._waitResponse) {
            return this;
        }

        // Make request.
        var xhr = $.ajax({
            url: window.ajaxurl,
            type: 'post',
            timeout: 45000, // throw an error if not completed after 15 sec.
            data: {
                action: config.action,
                actionName: config.actionExportCategories
            },
            // We awaiting JSON response from WordPress
            dataType: 'json',
            jsonp: false,
            cache: false
        })
            .done(this.done)
            .fail(this.fail);

        this._waitResponse = true;
    },

    done: function(data, textStatus, jqXHR) {
        this._waitResponse = false;
        Backbone.trigger('setka:workflow:exportTerm:done', data, textStatus, jqXHR);
    },

    fail: function (jqXHR, textStatus, errorThrown) {
        this._waitResponse = false;
        Backbone.trigger('setka:workflow:exportTerm:fail', jqXHR, textStatus, errorThrown);
    }
});
