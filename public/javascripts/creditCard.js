$(document).ready(function() {
    $("tbody > tr").click(function () {
        $(this).addClass('selected').siblings().removeClass('selected');
    });

    $('.remove').on('click', function (e) {
        if ($("tbody > tr.selected td:nth-child(2)").html()) {
            window.location.replace("/removeCreditcard/" + $(".selected > td:nth-child(2)").html());
        }
    });

    $('.detail').on('click', function (e) {
        if ($(" tbody > tr.selected td:nth-child(2)").html()) {
            window.location.replace("/modifyCreditcard/" + $(".selected > td:nth-child(2)").html());
        }
    });

    $('.add').on('click', function (e) {
        window.location.replace("/addCreditcard/");

    });

    $('.favorite').on('click', function (e) {
        if ($("tbody > tr.selected td:nth-child(2)").html()) {
            window.location.replace("/favoriteCreditcard/" + $(".selected > td:nth-child(2)").html());
        }
    });
});