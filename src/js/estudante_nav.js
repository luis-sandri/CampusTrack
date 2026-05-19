document.addEventListener("DOMContentLoaded", function () {
    var params = new URLSearchParams(window.location.search);
    var idInstituicao = params.get("id") || params.get("id_instituicao");
    var links = document.querySelectorAll(".ct-mobile-tabbar a");

    if (!idInstituicao || !/^\d+$/.test(idInstituicao)) {
        return;
    }

    for (var i = 0; i < links.length; i++) {
        var link = links[i];
        var aba = link.getAttribute("data-tab");
        var query = "?id=" + encodeURIComponent(idInstituicao);

        if (aba === "mapa") {
            link.href = "../visitante/instituicao.html" + query;
        } else {
            link.href = aba + ".html" + query;
        }
    }
});
