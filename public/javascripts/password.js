$(document).ready(function() {
    $("tbody > tr").click(function(){
        $(this).addClass('selected').siblings().removeClass('selected');
    });

    $("tbody > tr").dblclick(function(){
        copyText = $(this).find(".password")[0];
        copyText.select();

        document.execCommand("copy");
    });

    $('.remove').on('click', function(e){
        console.log($("#user > tr.selected td:first").html());
        if ($("tbody > tr.selected td:first").html()){
            window.location.replace("/removePassword/"+$(".selected > td:nth-child(2)").html());
        }
    });

    $('.removesh').on('click', function(e){
        console.log($("#share > tr.selected td:first").html());
        if ($("#share > tr.selected td:first").html()){
            window.location.replace("/removePasswordSh/"+$(".selected > td:nth-child(2)").html());
        }
    });

    $('.share').on('click', function(e){
        if ($("#user > tr.selected td:first").html()){
            window.location.replace("/sharePassword/"+$(".selected > td:nth-child(2)").html());
        }
    });

    $('.detail').on('click', function(e){
        if ($("#user > tr.selected td:first").html()){
            window.location.replace("/modifyPassword/"+$(".selected > td:nth-child(2)").html());
        }
    });

    $('.add').on('click', function(e){
        window.location.replace("/addPassword/");
    });


    $('.favorite').on('click', function(e){
        if ($("#user > tr.selected td:first").html()){
            window.location.replace("/favoritePassword/"+$("#user > .selected > td:nth-child(2)").html());
        }
    });
});