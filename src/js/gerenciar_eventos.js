document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarEventosPendentes();
});

async function carregarEventosPendentes() {
    var lista = document.getElementById("lista");
    lista.innerHTML = '<p class="text-muted">Carregando...</p>';

    const retorno = await fetch("../../php/evento_pendente_get.php");
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
            '<th>Organização</th>' +
            '<th>Ações</th>' +
        '</tr></thead><tbody>';

    if (resposta.data.length === 0) {
        html += '<tr><td colspan="5" class="text-center text-muted">Nenhum evento pendente de aprovação.</td></tr>';
    } else {
        for (var i = 0; i < resposta.data.length; i++) {
            var e = resposta.data[i];

            if (!e.id_evento || !e.nome) {
                alert("ERRO! Evento com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + textoSeguro(e.nome) + '</td>' +
                '<td>' + textoSeguro(e.data_formatada || e.data) + '</td>' +
                '<td>' + textoSeguro(e.nome_local) + '</td>' +
                '<td>' + textoSeguro(e.nome_organizacao) + '</td>' +
                '<td>' +
                    '<button class="btn btn-success btn-sm me-2" onclick="aprovar(' + e.id_evento + ')">Aprovar</button>' +
                    '<button class="btn btn-danger btn-sm" onclick="recusar(' + e.id_evento + ')">Recusar</button>' +
                '</td>' +
            '</tr>';
        }
    }

    html += '</tbody></table>';
    lista.innerHTML = html;
}

async function aprovar(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja aprovar este evento?")
        : confirm("Tem certeza que deseja aprovar este evento?");

    if (!confirmado) {
        return;
    }

    await alterarStatus(id, "ativo");
}

async function recusar(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja recusar este evento?")
        : confirm("Tem certeza que deseja recusar este evento?");

    if (!confirmado) {
        return;
    }

    await alterarStatus(id, "recusado");
}

async function alterarStatus(id, novo_status) {
    var formData = new FormData();
    formData.append("id_evento", id);
    formData.append("novo_status", novo_status);

    const retorno = await fetch("../../php/evento_alterar_status.php", {
        method: "POST",
        body: formData,
    });
    const resposta = await retorno.json();

    if (resposta.status === "ok") {
        alert(resposta.mensagem);
        window.location.reload();
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
