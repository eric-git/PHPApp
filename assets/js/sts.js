$(document).ready(function () {
    hljs.highlightAll();

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        $(this).addClass("disabled");
        $.post("/Controllers/AjaxManager.php",
            { "controller": "StsController", "function": "issue" },
            function (data) {
                var response = JSON.parse(data);
                $("#txtRequest").html($("<div/>").text(response.Request).html());
                $("#txtResponse").html($("<div/>").text(response.Response).html());
                hljs.highlightAll();
                $("#txtRequest, #txtResponse").closest("pre").scrollTop(0);
            })
            .always(function () {
                $("#btnSubmit").removeClass("disabled");
            });
    });
});
