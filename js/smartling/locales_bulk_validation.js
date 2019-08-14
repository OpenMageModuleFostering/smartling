Validation.add('sl-required-entry-bulk', 'One of checkboxes should be choosen', function(v) {

var checkedList = [];
    $$('.sl-required-entry-bulk').each(function(ele) {

        if ($(ele).checked)
        {
            checkedList.push($(ele).name);
        }
    });

    return (checkedList.length > 0);
});
