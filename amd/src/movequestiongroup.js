define(['jquery', 'jqueryui'], function($, jqui) {
    return {
        init: function(cmid) {
            // Move question group within groups or items.
            $("div#individualfeedback_dragarea form").sortable({
                items: $('div.questiongroupmoveitem'),
                handle: '.movequestiongroup',
                containment: $('#individualfeedback_dragarea'),
                update: function (event, ui) {
                    // Get all the question items in the new order.
                    var itemorder = [];
                    var counter = 0;
                    $('div.individualfeedback_itemlist').each(function() {
                        var elementid = $(this).attr('id');
                        var startpos = elementid.indexOf('_item_') + 6;
                        var itemid = elementid.substr(startpos);
                        itemorder[counter] = itemid;
                        counter++;
                    });

                    // Save the new order in the db.
                    $.ajax({
                        type: "POST",
                        url: M.cfg.wwwroot + "/mod/individualfeedback/ajax.php",
                        data: { action: 'savequestiongroupitemorder',
                                id: cmid,
                                itemorder: itemorder.toString(),
                                sesskey: M.cfg.sesskey},
                        dataType: "json",
                        fail: function (data) {
                        }
                    });

                }
            }).disableSelection();
        }
    };
});
