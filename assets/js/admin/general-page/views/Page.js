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
