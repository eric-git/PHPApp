$(document).ready(function () {
    hljs.highlightAll();

    $("#btnSubmit").click(function (e) {
        e.preventDefault();
        $("#txtResponse").html("").closest("details").removeAttr("open");
        $("#txtRequest").prop("disabled", "disabled");
        $(this).addClass("disabled");
        var domParser = new DOMParser();
        var xml = domParser.parseFromString($("#txtRequest").text(), "text/xml");
        var serializer = new XMLSerializer();
        $.post("/Controllers/AjaxManager.php",
            {
                controller: "StsController",
                function: "issue",
                param_0: serializer.serializeToString(xml)
            },
            function (data) {
                $("txtResponse").Html(data);
            });
     });
});