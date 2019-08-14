Validation.add('sl-required-entry-multy', 'One of checkboxes should be choosen and appropriates locale is entered', function(v) {
         
    var checkedList = [];
    $$('.sl-required-entry-multy').each(function(ele) {
        
        if ($(ele).checked && $(ele).next('input[type=text]').getValue().length > 0)
        {
            checkedList.push($(ele).name);
        }
    });
    
    return (checkedList.length > 0);
});