$(document).ready(function() {
    function generatePassword() {
        var length = 40;
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
            retVal = "";
        for (var i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        return retVal;
    }

    $('.generate').on('click', function (e) {
        $('#password').val(generatePassword());
    });
});