$(document).ready(function () {
    $(".stack .button").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        $(".stack .button").removeClass("primary");
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
        $("#txtData").val(xml);
        $("#txtResponse, #txtRequest").html("");
    });

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        var formData = $("form").serialize();
        $(".button").addClass("disabled");
        $("#txtData").attr("disabled", "");
        $("#txtRequest, #txtResponse").html("");
        $.ajax({
            data: formData
        }).done(function (data) {
            $("#txtRequest").html(convertToHtml(data.UsiRequest));
            $("#txtResponse").html(convertToHtml(data.UsiResponse));
            hljs.highlightAll();
            $("#txtRequest, #txtResponse").scrollTop(0);
        }).always(function () {
            $(".button").removeClass("disabled");
            $("#txtData").removeAttr("disabled");
        });
    });

    $(".stack .button:first").click();
});

function getCurrentOperation() {
    return $(".stack .button.primary").text();
}