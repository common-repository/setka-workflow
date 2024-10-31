(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/* global jQuery, Backbone */

var setkaWorkflowAdminGeneralPage = {};

// Store everything globally
window.setkaWorkflowAdminGeneralPage = setkaWorkflowAdminGeneralPage;

setkaWorkflowAdminGeneralPage.views = {
    Page: require('./views/Page'),
    ExportButton: require('./views/ExportButton'),
    PostAuthorId: require('./views/PostAuthorId')
};

setkaWorkflowAdminGeneralPage.utils = {
    ExportTerms: require('./utils/ExportTerms')
};

},{"./utils/ExportTerms":2,"./views/ExportButton":3,"./views/Page":4,"./views/PostAuthorId":5}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
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

},{}],4:[function(require,module,exports){
/* global jQuery, Backbone, module */
var
    adapter      = window.setkaWorkflowAdminGeneralPage,
    $            = jQuery,
    config       = window.setkaWorkflowAdminGeneralPageConfig,
    translations = window.setkaWorkflowAdminGeneralPageConfig.translations;

module.exports = Backbone.View.extend({

    views: {},

    utils: {},

    DOM: {
        exportButton: null,
        postAuthorId: null,
        errors: null,
        log: null,
        progress: null
    },

    initialize: function() {
        _.bindAll(this, 'onExportButtonClick', 'onExported', 'onExportFailed', 'exportTerm');
        this
            .setupDOM()
            .setupUtils()
            .addEvents();
    },

    setupDOM: function() {

        this.DOM.exportButton = new adapter.views.ExportButton({
            el: config.elements.exportButton,
            model: this.model
        });

        this.DOM.postAuthorId = new adapter.views.PostAuthorId({
           model: this.model
        });

        this.DOM.errors       = $(config.elements.exportErrors);
        this.DOM.log          = $(config.elements.exportLog);

        return this;
    },

    setupUtils: function() {
        this.utils.exportTerms = new adapter.utils.ExportTerms();

        return this;
    },

    addEvents: function() {
        this.DOM.exportButton.$el.click(this.onExportButtonClick);

        Backbone.on('setka:workflow:exportTerm:done', this.onExported);
        Backbone.on('setka:workflow:exportTerm:fail', this.onExportFailed);

        return this;
    },

    onExportButtonClick: function(event) {
        event.preventDefault();

        this
            .initialExport()
            .exportTerm();
    },

    /**
     * Runs the progress bar filled to 100% with animation.
     */
    initialExport: function() {

        // Disable export button.
        this.DOM.exportButton
            .disable();

        // Run progress bar with 100% fill.
        this.DOM.progress = $(config.elements.exportProgress)
            .removeClass(config.elements.hidden)
            .progressbar({value: false});

        // Hide errors.
        this.DOM.errors
            .addClass(config.elements.hidden);

        // Hide log.
        this.DOM.log
            .addClass(config.elements.hidden);

        return this;
    },

    onExported: function(data, textStatus, jqXHR) {

        if(data.stat.notCreated === 0) {
            // If all terms exported hide progress bar.
            this.DOM.progress
                .addClass(config.elements.hidden);
        } else {
            // If queue is still full - update the progress bar value.
            var process = (data.stat.created * 100) / data.stat.total;

            // Minimum value.
            if(process < 5) {
                process = 5;
            }

            // Update progress bar.
            this.DOM.progress
                .removeClass(config.elements.hidden)
                .progressbar({value: process});
        }

        // Show errors.
        if(!_.isUndefined(data.errors)) {

            // Insert errors into container.
            _.each(data.errors, function(element, index, list) {
                $(this.DOM.errors).text(element.message);
            }, this);

            // Enable errors container.
            $(this.DOM.errors)
                .removeClass(config.elements.hidden);

        } else {
            // Hide errors container.
            $(this.DOM.errors)
                .addClass(config.elements.hidden);
        }

        // Show result of operation.
        if(!_.isUndefined(data.entity)) {

            if(data.stat.notCreated === 0) {
                // All terms exported.
                $(this.DOM.log)
                    .text(translations.exportSuccessfulFinished);
            } else {
                // There is more terms to export - show current progress.
                $(this.DOM.log)
                    .text(translations.exportedResult.replace('%1$s', data.entity.wordpress.name));
            }

            // Show result container.
            $(this.DOM.log)
                .removeClass(config.elements.hidden);
        } else {
            // Hide result container.
            $(this.DOM.log)
                .addClass(config.elements.hidden);
        }

        // If more terms exists lets export it.
        if(data.stat.notCreated > 0 && _.isUndefined(data.errors)) {
            // If more terms exists and no errors then export next one.
            this.exportTerm();
        } else {
            // Disable progress.
            this.DOM.progress
                .addClass(config.elements.hidden);

            // Enable export button.
            this.DOM.exportButton
                .enable();
        }
    },

    onExportFailed: function(jqXHR, textStatus, errorThrown) {
        // If export failed.

        // Hide progress bar.
        this.DOM.progress
            .addClass(config.elements.hidden);

        // Hide log.
        this.DOM.log
            .addClass(config.elements.hidden);

        // Show connection error.
        $(this.DOM.errors)
            .text(translations.connectToWordPressError)
            .removeClass(config.elements.hidden);

        // Enable export button.
        this.DOM.exportButton
            .enable();
    },

    exportTerm: function() {
        this.utils.exportTerms.exportTerm();
        return this;
    }
});

},{}],5:[function(require,module,exports){
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

},{}]},{},[1])
//# sourceMappingURL=general-page.js.map
