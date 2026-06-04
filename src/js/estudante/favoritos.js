document.addEventListener("DOMContentLoaded", function () {
    valida_sessao().then(function (ok) {
        if (ok) {
            carregarFavoritos();
        }
    });
});

async function carregarFavoritos() {
    var lista = document.getElementById("lista-favoritos");

    if (!lista) {
        return;
    }

    lista.innerHTML = "<p class=\"text-muted\">Carregando favoritos...</p>";

    var retorno = await CampusTrack.api.json("../../php/favoritos/get.php");

    if (retorno.status !== "ok") {
        lista.innerHTML = "<p class=\"text-danger\">Erro: " + CampusTrack.dom.escapeHtml(CampusTrack.resposta.mensagem(retorno)) + "</p>";
        return;
    }

    var registros = retorno.data;

    if (!Array.isArray(registros) || registros.length === 0) {
        lista.innerHTML =
            "<div class=\"bg-white border rounded-3 p-4 p-md-5 text-center shadow-sm\">" +
                "<div class=\"ct-favorite-star display-5 mb-3\" aria-hidden=\"true\">&#9734;</div>" +
                "<p class=\"lead text-muted mb-1\">Voce ainda nao tem nenhum local favoritado.</p>" +
                "<p class=\"text-muted small mb-0\">Acesse o mapa da sua instituicao e clique na estrela para salvar um local.</p>" +
            "</div>";
        return;
    }

    var html = "<div class=\"row g-3\">";

    for (var i = 0; i < registros.length; i++) {
        var local = registros[i];
        html +=
            "<div class=\"col-md-6 col-lg-4\">" +
                "<div class=\"card ct-favorite-card h-100 shadow-sm\">" +
                    "<div class=\"card-body\">" +
                        "<div class=\"d-flex justify-content-between align-items-start gap-3 mb-2\">" +
                            "<div class=\"d-flex align-items-start gap-2\">" +
                                "<span class=\"ct-favorite-star\" aria-hidden=\"true\">&#9733;</span>" +
                                "<h2 class=\"h6 fw-bold mb-0 text-primary\">" + CampusTrack.dom.escapeHtml(local.nome) + "</h2>" +
                            "</div>" +
                            "<div class=\"d-flex gap-2\">" +
                                "<a href=\"../visitante/instituicao.html?id_instituicao=" + CampusTrack.dom.escapeHtml(local.id_instituicao) + "&rota_para=" + CampusTrack.dom.escapeHtml(local.id_local) + "\" " +
                                    "class=\"btn btn-sm btn-primary\" " +
                                    "title=\"Ver no mapa e traçar rota\">Ir para local</a>" +
                                "<button " +
                                    "class=\"btn btn-sm btn-outline-danger ct-btn-remover-favorito\" " +
                                    "data-id-local=\"" + CampusTrack.dom.escapeHtml(local.id_local) + "\" " +
                                    "title=\"Remover dos favoritos\" " +
                                    "aria-label=\"Remover " + CampusTrack.dom.escapeHtml(local.nome) + " dos favoritos\"" +
                                ">Remover</button>" +
                            "</div>" +
                        "</div>" +
                        "<p class=\"small text-muted mb-1\">" +
                            "<span class=\"fw-medium\">Instituicao:</span> " + CampusTrack.dom.escapeHtml(local.nome_instituicao) +
                        "</p>" +
                        "<p class=\"small text-muted mb-1\">" +
                            "<span class=\"fw-medium\">Escola:</span> " + CampusTrack.dom.escapeHtml(local.tipo_escola) + " - " + CampusTrack.dom.escapeHtml(local.tipo) +
                        "</p>" +
                        "<p class=\"small text-muted mb-0\">" +
                            "<span class=\"fw-medium\">Capacidade:</span> " + CampusTrack.dom.escapeHtml(local.capacidade) + " pessoas" +
                        "</p>" +
                    "</div>" +
                "</div>" +
            "</div>";
    }

    html += "</div>";
    lista.innerHTML = html;

    var botoes = lista.querySelectorAll(".ct-btn-remover-favorito");
    for (var j = 0; j < botoes.length; j++) {
        botoes[j].addEventListener("click", aoClicarRemover);
    }
}

async function aoClicarRemover(event) {
    var botao = event.currentTarget;
    var idLocal = botao.getAttribute("data-id-local");

    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Deseja remover este local dos seus favoritos?")
        : confirm("Deseja remover este local dos seus favoritos?");

    if (!confirmado) {
        return;
    }

    botao.disabled = true;
    botao.textContent = "Removendo...";

    var formData = new FormData();
    formData.append("id_local", idLocal);

    var retorno = await CampusTrack.api.json("../../php/favoritos/remover.php", {
        method: "POST",
        body: formData,
    });

    if (retorno.status === "ok") {
        alert(CampusTrack.resposta.mensagem(retorno));
        carregarFavoritos();
    } else {
        alert("Erro: " + CampusTrack.resposta.mensagem(retorno));
        botao.disabled = false;
        botao.textContent = "Remover";
    }
}
