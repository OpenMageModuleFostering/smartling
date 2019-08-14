function singleContentActions(url, params) {
    new Ajax.Request(url, {
        method: 'post',
        parameters: params,
        onComplete: function(transport){
        var response = transport.responseText.evalJSON(true);
        showMessage(response);
        }
    });
}

function showMessage(response){
    var block = "<div id='result'>" + response.messageblock + "</div>";
    $('result').replace(block);
}


