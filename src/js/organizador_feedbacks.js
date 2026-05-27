document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarFeedbacks();
});

function textoSeguro(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function carregarFeedbacks() {
    var lista = document.getElementById("lista");
    lista.innerHTML = '<div class="col-12"><p class="text-muted">Carregando...</p></div>';

    try {
        const retorno = await fetch("../../php/organizador_feedbacks_get.php");
        const resposta = await retorno.json();

        if (resposta.status !== "ok") {
            lista.innerHTML = '<div class="col-12"><div class="alert alert-danger">ERRO! ' + textoSeguro(resposta.mensagem) + '</div></div>';
            return;
        }

        if (!Array.isArray(resposta.data)) {
            lista.innerHTML = '<div class="col-12"><div class="alert alert-danger">ERRO! Formato inválido recebido do servidor.</div></div>';
            return;
        }

        if (resposta.data.length === 0) {
            lista.innerHTML = '<div class="col-12"><div class="alert alert-info">Nenhum feedback recebido ainda.</div></div>';
            return;
        }

        var html = '';
        for (var i = 0; i < resposta.data.length; i++) {
            var fb = resposta.data[i];
            
            html += `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 bg-white">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-2">Evento</span>
                            <small class="text-muted">${textoSeguro(fb.evento_data_formatada)}</small>
                        </div>
                        <h5 class="card-title text-dark fw-bold mb-1">${textoSeguro(fb.evento_nome)}</h5>
                        <p class="card-text text-secondary fst-italic flex-grow-1">"${textoSeguro(fb.comentario)}"</p>
                        <hr class="my-2">
                        <small class="text-muted fw-semibold">Avaliado por: ${textoSeguro(fb.aluno_nome)}</small>
                    </div>
                </div>
            </div>`;
        }
        
        lista.innerHTML = html;

    } catch (erro) {
        lista.innerHTML = '<div class="col-12"><div class="alert alert-danger">Erro de conexão ao carregar feedbacks.</div></div>';
        console.error(erro);
    }
}
