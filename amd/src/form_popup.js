define([
    "jquery",
    "local_kdashboard/maskedinput",
    "local_kdashboard/validate",
], function($, validator) {
    return {
        init: function() {

            if ($(".kopere-modal-content form.validate").length) {
                $(".kopere-modal-content form.validate").validator();
            }

            $('.kopere-modal-content .button-actions').click(function(event) {
                event.stopImmediatePropagation();
            });
        }
    };
});
