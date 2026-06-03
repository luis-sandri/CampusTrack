document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarMeusEventos();
});

function badgeStatus(status) {
    var mapa = {
        "pendente": "warning",
        "ativo": "success",
        "recusado": "danger",
        "encerrado": "secondary"
    };
    var cor = mapa[status] || "secondary";
    return '<span class="badge text-bg-' + cor + '">' + textoSeguro(status) + '</span>';
}

async function carregarMeusEventos() {
    var lista = document.getElementById("lista");
    lista.innerHTML = '<p class="text-muted">Carregando...</p>';

    const resposta = await ctJson("../../php/organizador_meus_eventos_get.php");

    if (resposta.status !== "ok") {
        lista.innerHTML = "";
        alert("ERRO! " + ctMensagem(resposta));
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
            '<th>Instituicao</th>' +
            '<th>Status</th>' +
            '<th>Acoes</th>' +
        '</tr></thead><tbody>';

    if (resposta.data.length === 0) {
        html += '<tr><td colspan="6" class="text-center text-muted">Nenhum evento cadastrado.</td></tr>';
    } else {
        for (var i = 0; i < resposta.data.length; i++) {
            var e = resposta.data[i];

            if (!e.id_evento || !e.nome || !e.status) {
                alert("ERRO! Evento com dados incompletos no retorno do servidor.");
                return;
            }

            var acoes = '<div class="btn-group">';
            if (e.status === "ativo") {
                acoes += '<button class="btn btn-sm btn-outline-warning" onclick="encerrarEvento(' + e.id_evento + ')">Encerrar</button>';
            }
            acoes += '<button class="btn btn-sm btn-outline-danger" onclick="excluirEvento(' + e.id_evento + ')">Excluir</button>';
            acoes += '</div>';

            html += '<tr>' +
                '<td>' + textoSeguro(e.nome) + '</td>' +
                '<td>' + textoSeguro(e.data_formatada || e.data) + '</td>' +
                '<td>' + textoSeguro(e.nome_local) + '</td>' +
                '<td>' + textoSeguro(e.nome_instituicao) + '</td>' +
                '<td>' + badgeStatus(e.status) + '</td>' +
                '<td>' + acoes + '</td>' +
            '</tr>';
        }
    }

    html += '</tbody></table>';
    lista.innerHTML = html;
}

window.encerrarEvento = async function (id_evento) {
    if (!confirm("Tem certeza que deseja encerrar este evento manualmente? Estudantes ainda poderao avalia-lo.")) {
        return;
    }

    var dados = new FormData();
    dados.append("id_evento", id_evento);

    const resposta = await ctJson("../../php/evento_encerrar.php", {
        method: "POST",
        body: dados
    });

    if (resposta.status === "ok") {
        alert(ctMensagem(resposta));
        carregarMeusEventos();
    } else {
        alert("ERRO! " + ctMensagem(resposta));
    }
};

window.excluirEvento = async function (id_evento) {
    if (!confirm("Tem certeza que deseja EXCLUIR este evento? Esta acao nao pode ser desfeita.")) {
        return;
    }

    var dados = new FormData();
    dados.append("id_evento", id_evento);

    const resposta = await ctJson("../../php/evento_excluir.php", {
        method: "POST",
        body: dados
    });

    if (resposta.status === "ok") {
        alert(ctMensagem(resposta));
        carregarMeusEventos();
    } else {
        alert("ERRO! " + ctMensagem(resposta));
    }
};
