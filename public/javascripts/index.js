$(document).ready(function() {
    $("tbody > tr").dblclick(function(){
        copyText = $(this).find(".password")[0];
        copyText.select();

        document.execCommand("copy");
    });
});