$(document).ready(function () {
    $("#cbEnvironment").change(function () {
        $("[id^='env-'], [id^='org-']").hide();
        $("#env-" + $(this).val()).show();
        var orgComboBox = $("#org-" + $(this).val()).show().find("select");
        orgComboBox.val($("option[data-current='true']", orgComboBox).val() || $("option:first", orgComboBox).val()).change();
    });

    $("[id^='org-'] select").change(function () {
        $("[id^='key-']").hide();
        $("#key-" + $("#cbEnvironment").val() + "-" + $(this).val()).show();
    });

    $("#btnReset").click(function () {
        var environmentComboBox = $("#cbEnvironment");
        environmentComboBox.val($("option[data-current='true']", environmentComboBox).val() || $("option:first", environmentComboBox).val()).change();
    });

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        if ($(this).hasClass("disabled") || $(this).hasClass("primary")) {
            return;
        }

        $("[name='param_1']:hidden").attr("disabled", "disabled");
        var formData = $("form").serialize();
        $(".button, select").addClass("disabled");
        $.ajax({
            data: formData
        }).done(function (data) {
            $("#cbEnvironment option").each(function () {
                $(this).attr("data-current", $(this).val() === data.Environment);
            });
            $("#org-" + data.Environment + " option").each(function () {
                $(this).attr("data-current", $(this).val() === data.OrgCode);
            });
            $("#txtCurrentEnvironment").text(data.Environment);
            $("#txtCurrentOrgCode").text(data.OrgCode);
            $("#btnReset").click();
        }).always(function () {
            $(".button, select").removeClass("disabled");
            $("[name='param_1']:hidden").removeAttr("disabled");
        });
    });

    $("aside [data-section]").click(function () {
        $(this).addClass("primary").siblings().removeClass("primary");
        $("aside [data-section]").each(function () {
            $("#" + $(this).data("section")).toggle($(this).hasClass("primary"));
        });
    });

    $("#btnReset").click();
});