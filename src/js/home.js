document.addEventListener("DOMContentLoaded", function () {
    var botoes = document.querySelectorAll(".js-instituicao");
    for (var i = 0; i < botoes.length; i++) {
        botoes[i].addEventListener("click", function () {
            var id = this.getAttribute("data-id");
            window.location.href = "estudante/login.html?id_instituicao=" + encodeURIComponent(id);
        });
    }

    document.getElementById("btn-organizacao").addEventListener("click", function () {
        window.location.href = "visitante/login.html?tipo=organizacao";
    });

    document.getElementById("btn-gerente").addEventListener("click", function () {
        window.location.href = "gerente/login.html";
    });
});
