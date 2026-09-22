define(['jquery'], function($) {
    return {
        init: function() {
            // Only show filtered question groups.
            $('select.questiongroup_select').on('change', function() {
                var selectedvalue = $(this).val();
                if (selectedvalue > 0) {
                    $('.questiongroup_analysed').each(function() {
                        $(this).toggle(false);
                    });
                    $('#questiongroup_' + selectedvalue).toggle(true);
                } else {
                    $('.questiongroup_analysed').each(function() {
                        $(this).toggle(true);
                    });
                }
            });
        }
    };
});
