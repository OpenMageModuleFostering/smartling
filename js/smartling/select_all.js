
function selectAll(obj) {
    
    var form = $(obj).up('form');
    
    var status = $(obj).childElements()[0].visible();
        
    var i=form.getElements('checkbox');
    i.each(function(item)
        {
            item.checked = status;
        }
    );

    $(obj).childElements().each(function(node){
        $(node).toggle();
    });
    
}

