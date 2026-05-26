document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarMeusEventos();
});

function badgeStatus(status) {
    var mapa = {
        "pendente": "warning",
        "ativo":    "success",
        "recusado": "danger"
    };
    var cor = mapa[status] || "secondary";
    return '<span class="badge text-bg-' + cor + '">' + textoSeguro(status) + '</span>';
}

async function carregarMeusEventos() {
    var lista = document.getElementById("lista");
    lista.innerHTML = '<p class="text-muted">Carregando...</p>';

    const retorno = await fetch("../../php/organizador_meus_eventos_get.php");
    const resposta = await retorno.json();

    if (resposta.status !== "ok") {
        lista.innerHTML = "";
        alert("ERRO! " + resposta.mensagem);
        return;
    }

    if (!Array.isArray(resposta.data)) {
        lista.innerHTML = "";
        alert("ERRO! Lista de eventos invalida no retorno do servidor.");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>Nome</th>' +
            '<th>Data</th>' +
            '<th>Local</th>' +
            '<th>Instituição</th>' +
            '<th>Status</th>' +
        '</tr></thead><tbody>';

    if (resposta.data.length === 0) {
        html += '<tr><td colspan="5" class="text-center text-muted">Nenhum evento cadastrado.</td></tr>';
    } else {
        for (var i = 0; i < resposta.data.length; i++) {
            var e = resposta.data[i];

            if (!e.id_evento || !e.nome || !e.status) {
                alert("ERRO! Evento com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + textoSeguro(e.nome) + '</td>' +
                '<td>' + textoSeguro(e.data_formatada || e.data) + '</td>' +
                '<td>' + textoSeguro(e.nome_local) + '</td>' +
                '<td>' + textoSeguro(e.nome_instituicao) + '</td>' +
                '<td>' + badgeStatus(e.status) + '</td>' +
            '</tr>';
        }
    }

    html += '</tbody></table>';
    lista.innerHTML = html;
}
