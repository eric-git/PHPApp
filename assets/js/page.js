function convertToHtml(xml) {
    return $("<div/>").text(xml).html();
}

$.ajaxSetup({
    url: "/Ajax/",
    type: "POST",
    dataType: "json"
});

$(document).ajaxStart(function () {
    toastr.clear();
    toastr.info("Invoking...");
}).ajaxSuccess(function () {
    toastr.clear();
    toastr.success("Done!");
}).ajaxError(function () {
    toastr.clear();
    toastr.error("Error occurred.");
});

toastr.options = {
    "showDuration": "500",
    "hideDuration": "500",
    "timeOut": "2000",
    "extendedTimeOut": "500"
};