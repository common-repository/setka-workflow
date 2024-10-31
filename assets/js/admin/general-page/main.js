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
