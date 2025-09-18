function checkstartendgroupitem() {
    $('div.individualfeedback_questiongroup_start').each(function() {
        var currentquestiongroup = $(this);
        var childelements = $(this).children('.individualfeedback_itemlist');
        var numberofelements = childelements.length;
        if (numberofelements) {
            firstchild = childelements[0];
            if (!$(firstchild).hasClass('individualfeedback-item-questiongroup')) {
                $(firstchild).insertBefore($(this));
            }

            lastchild = childelements[numberofelements - 1];
            if (!$(lastchild).hasClass('individualfeedback-item-questiongroupend')) {
                $(lastchild).insertAfter($(this));
            }
        }
    });
}