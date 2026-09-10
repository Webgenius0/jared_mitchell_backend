function formatState(e){var t;return e.id?((t=$('<span><img class="img-flag rounded" height="18" /> <span></span></span>')).find("span").text(e.text),t.find("img").attr("src","/admin/assets/images/flags/select2/"+e.element.value.toLowerCase()+".png"),t):e.text}
$(document).ready(function(){
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        if ($(".js-example-basic-single").length) $(".js-example-basic-single").select2();
        if ($(".js-example-basic-multiple").length) $(".js-example-basic-multiple").select2();
        if ($(".js-example-data-array").length) $(".js-example-data-array").select2({data:[{id:0,text:"enhancement"},{id:1,text:"bug"},{id:2,text:"duplicate"},{id:3,text:"invalid"},{id:4,text:"wontfix"}]});
        if ($(".js-example-templating").length) $(".js-example-templating").select2({templateResult:formatState});
        if ($(".select-flag-templating").length) $(".select-flag-templating").select2({templateSelection:formatState});
        if ($(".js-example-disabled").length) $(".js-example-disabled").select2();
        if ($(".js-example-disabled-multi").length) $(".js-example-disabled-multi").select2();
    }
});
$(document).on("click", ".js-programmatic-enable", function(){
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        $(".js-example-disabled").prop("disabled",!1);
        $(".js-example-disabled-multi").prop("disabled",!1);
    }
});
$(document).on("click", ".js-programmatic-disable", function(){
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        $(".js-example-disabled").prop("disabled",!0);
        $(".js-example-disabled-multi").prop("disabled",!0);
    }
});