document.addEventListener("DOMContentLoaded", function () {
    carregarEventos();
});

function textoSeguroEvento(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function mostrarAlertaEventos(mensagem, tipo) {
    var alerta = document.getElementById("alerta-eventos");
    alerta.textContent = mensagem;
    alerta.className = "alert alert-" + tipo;
}

function montarUrlEventos() {
    var params = new URLSearchParams(window.location.search);
    var idInstituicao = params.get("id") || params.get("id_instituicao");
    var url = "../../php/evento_get.php";

    if (idInstituicao && /^\d+$/.test(idInstituicao)) {
        url += "?id_instituicao=" + encodeURIComponent(idInstituicao);
    }

    return url;
}

async function carregarEventos() {
    var lista = document.getElementById("lista-eventos");
    lista.innerHTML = '<div class="col-12 text-secondary">Carregando eventos...</div>';

    try {
        const retorno = await fetch(montarUrlEventos());
        const resposta = await retorno.json();

        if (resposta.status !== "ok") {
            lista.innerHTML = "";
            mostrarAlertaEventos(resposta.mensagem || "Nao foi possivel carregar os eventos.", "danger");
            return;
        }

        if (!Array.isArray(resposta.data)) {
            lista.innerHTML = "";
            mostrarAlertaEventos("Lista de eventos invalida no retorno do servidor.", "danger");
            return;
        }

        if (resposta.data.length === 0) {
            lista.innerHTML =
                '<div class="col-12">' +
                    '<section class="bg-white border rounded-3 p-4 text-center shadow-sm">' +
                        '<h2 class="h5 fw-bold text-primary">Nenhum evento disponivel</h2>' +
                        '<p class="text-secondary mb-0">Ainda nao existem eventos ativos para exibir.</p>' +
                    '</section>' +
                '</div>';
            return;
        }

        var html = "";
        for (var i = 0; i < resposta.data.length; i++) {
            var evento = resposta.data[i];

            html +=
                '<article class="col-12 col-md-6 col-xl-4">' +
                    '<div class="card ct-event-card h-100 shadow-sm">' +
                        '<div class="card-body">' +
                            '<h2 class="h5 card-title text-primary">' + textoSeguroEvento(evento.nome) + '</h2>' +
                            '<p class="ct-event-meta mb-2">' + textoSeguroEvento(evento.data_formatada || evento.data) + '</p>' +
                            '<p class="mb-1"><strong>Local:</strong> ' + textoSeguroEvento(evento.nome_local) + '</p>' +
                            '<p class="mb-1"><strong>Instituicao:</strong> ' + textoSeguroEvento(evento.nome_instituicao) + '</p>' +
                            '<p class="mb-0"><strong>Organizacao:</strong> ' + textoSeguroEvento(evento.nome_organizacao) + '</p>' +
                        '</div>' +
                    '</div>' +
                '</article>';
        }

        lista.innerHTML = html;
    } catch (erro) {
        lista.innerHTML = "";
        mostrarAlertaEventos("Erro de conexao ao carregar eventos.", "danger");
        console.error(erro);
    }
}
