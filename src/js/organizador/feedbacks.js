document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarFeedbacks();
});

async function carregarFeedbacks() {
    var lista = document.getElementById("lista");
    lista.innerHTML = '<div class="col-12"><p class="text-muted">Carregando...</p></div>';

    const resposta = await CampusTrack.api.json("../../php/organizadores/feedbacks.php");

    if (resposta.status !== "ok") {
        lista.innerHTML = '<div class="col-12"><div class="alert alert-danger">ERRO! ' + CampusTrack.dom.escapeHtml(CampusTrack.resposta.mensagem(resposta)) + '</div></div>';
        return;
    }

    if (!Array.isArray(resposta.data)) {
        lista.innerHTML = '<div class="col-12"><div class="alert alert-danger">ERRO! Formato invalido recebido do servidor.</div></div>';
        return;
    }

    if (resposta.data.length === 0) {
        lista.innerHTML = '<div class="col-12"><div class="alert alert-info">Nenhum feedback recebido ainda.</div></div>';
        return;
    }

    var html = "";
    for (var i = 0; i < resposta.data.length; i++) {
        var fb = resposta.data[i];

        html +=
            '<div class="col-12 col-md-6 col-lg-4">' +
                '<div class="card h-100 shadow-sm border-0 bg-white">' +
                    '<div class="card-body d-flex flex-column">' +
                        '<div class="d-flex justify-content-between align-items-start mb-2">' +
                            '<span class="badge bg-primary bg-opacity-10 text-primary mb-2">Evento</span>' +
                            '<small class="text-muted">' + CampusTrack.dom.escapeHtml(fb.evento_data_formatada) + '</small>' +
                        '</div>' +
                        '<h5 class="card-title text-dark fw-bold mb-1">' + CampusTrack.dom.escapeHtml(fb.evento_nome) + '</h5>' +
                        '<p class="card-text text-secondary fst-italic flex-grow-1">"' + CampusTrack.dom.escapeHtml(fb.comentario) + '"</p>' +
                        '<hr class="my-2">' +
                        '<small class="text-muted fw-semibold">Avaliado por: ' + CampusTrack.dom.escapeHtml(fb.aluno_nome) + '</small>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    lista.innerHTML = html;
}
