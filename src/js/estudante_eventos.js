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
        var agora = new Date();
        for (var i = 0; i < resposta.data.length; i++) {
            var evento = resposta.data[i];
            
            var dataEvento = new Date(evento.data.replace(' ', 'T')); // Safely parse date
            var fimEvento = new Date(dataEvento.getTime() + (120 * 60 * 1000));
            var isEncerrado = agora > fimEvento || evento.status === 'encerrado';
            var badgeStatus = isEncerrado ? '<span class="badge bg-secondary mb-2">Encerrado</span>' : '';

            var formFeedback = '';
            if (isEncerrado) {
                formFeedback = 
                    '<div class="mt-3 pt-3 border-top" id="container-feedback-' + evento.id_evento + '">' +
                        '<h3 class="h6 text-primary mb-2">Deixe seu feedback</h3>' +
                        '<form onsubmit="enviarFeedback(event, ' + evento.id_evento + ')">' +
                            '<textarea id="feedback-' + evento.id_evento + '" class="form-control mb-2" rows="2" maxlength="140" placeholder="O que achou do evento?" required></textarea>' +
                            '<button type="submit" class="btn btn-sm btn-primary w-100">Enviar Avaliação</button>' +
                        '</form>' +
                    '</div>';
            }

            html +=
                '<article class="col-12 col-md-6 col-xl-4">' +
                    '<div class="card ct-event-card h-100 shadow-sm">' +
                        '<div class="card-body">' +
                            badgeStatus +
                            '<h2 class="h5 card-title text-primary">' + textoSeguroEvento(evento.nome) + '</h2>' +
                            '<p class="ct-event-meta mb-2">' + textoSeguroEvento(evento.data_formatada || evento.data) + '</p>' +
                            '<p class="mb-1"><strong>Local:</strong> ' + textoSeguroEvento(evento.nome_local) + '</p>' +
                            '<p class="mb-1"><strong>Instituicao:</strong> ' + textoSeguroEvento(evento.nome_instituicao) + '</p>' +
                            '<p class="mb-0"><strong>Organizacao:</strong> ' + textoSeguroEvento(evento.nome_organizacao) + '</p>' +
                            formFeedback +
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

window.enviarFeedback = async function (event, idEvento) {
    event.preventDefault();
    const comentario = document.getElementById("feedback-" + idEvento).value.trim();
    if (!comentario) return;

    const data = new FormData();
    data.append("id_evento", idEvento);
    data.append("comentario", comentario);

    try {
        const retorno = await fetch("../../php/comentario_adicionar.php", {
            method: "POST",
            body: data
        });
        const resposta = await retorno.json();

        if (resposta.status === "ok") {
            if (window.__alertaOriginal) {
                window.__alertaOriginal(resposta.mensagem);
            } else {
                alert(resposta.mensagem);
            }
            const container = document.getElementById("container-feedback-" + idEvento);
            if (container) {
                container.innerHTML = '<p class="text-success mb-0 fw-bold">Agradecemos o seu feedback!</p>';
            }
        } else {
            alert(resposta.mensagem);
        }
    } catch (erro) {
        alert("Erro ao enviar avaliação.");
    }
};
