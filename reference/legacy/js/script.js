function printDiv(divId) {
    
    var id = "#" + divId;

    $("body").css("visibility","hidden");
    $(id).addClass("section-to-print");
    
    
    window.print();
    $("body").css("visibility","visible");

    $(id).removeClass("section-to-print");

}