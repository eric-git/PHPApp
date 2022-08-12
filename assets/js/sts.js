$(document).ready(function () {
    hljs.highlightAll();

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        $(".button").addClass("disabled");
        $("#txtResponse").html("");
        $.post("/Controllers/AjaxManager.php",
            {
                "controller": "StsController",
                "function": "issue"
            },
            function (data) {
                var response = JSON.parse(data);
                $("#txtRequest").html(convertToHtml(response.Request));
                $("#txtResponse").html(convertToHtml(response.Response));
                hljs.highlightAll();
                $("#txtRequest, #txtResponse").closest("pre").scrollTop(0);
            })
            .always(function () {
                $(".button").removeClass("disabled");
            });
    });
});
