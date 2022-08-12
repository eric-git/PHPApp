$(document).ready(function () {
    $(".operation-list .button").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }
        
        $(".operation-list .button").removeClass("primary");
        $(this).addClass("primary");
        $("#btnReset").click();
    });

    $("#btnReset").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }
        
        var operation = getCurrentOperation();
        var xml = $("script[id='" + operation + "']").text();
        $(":hidden[name='param_0']").val(operation);
        $("#txtRequest").val(xml);
        $("#txtResponse").html("");
    });

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        var formData = $("form").serialize();
        $(".button").addClass("disabled");
        $("#txtRequest").attr("disabled", "");
        $("#txtResponse").html("");
        $.post("/Controllers/AjaxManager.php",
            formData,
            function (data) {
                $("#txtResponse").html(convertToHtml(data));
                hljs.highlightAll();
                $("#txtResponse").closest("pre").scrollTop(0);
            })
            .always(function () {
                $(".button").removeClass("disabled");
                $("#txtRequest").removeAttr("disabled");
            });
    });

    $(".operation-list .button:first").click();
});

function getCurrentOperation() {
    return $(".operation-list .button.primary").text();
}