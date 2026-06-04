document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarDados();

    document.getElementById("novo").addEventListener("click", function () {
        window.location.href = "local_adicionar.html";
    });
});

async function carregarDados() {
    const resposta = await CampusTrack.api.json("../../php/locais/get.php?gerente=1");

    if (resposta.status !== "ok") {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
        return;
    }

    const registros = resposta.data;

    if (!Array.isArray(registros)) {
        alert("ERRO! Lista de locais invalida no retorno do servidor.");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>ID</th>' +
            '<th>Instituicao</th>' +
            '<th>Tipo escola</th>' +
            '<th>Tipo</th>' +
            '<th>Nome</th>' +
            '<th>Cap.</th>' +
            '<th>Long.</th>' +
            '<th>Lat.</th>' +
            '<th>Acoes</th>' +
        '</tr></thead><tbody>';

    if (registros.length === 0) {
        html += '<tr><td colspan="9" class="text-center text-muted">Nenhum local cadastrado.</td></tr>';
    } else {
        for (var i = 0; i < registros.length; i++) {
            var local = registros[i];

            if (!local.id_local || !local.id_instituicao || !local.nome_instituicao || !local.tipo_escola || !local.tipo || !local.nome || local.capacidade === null || local.capacidade === undefined || !local.longitude || !local.latitude) {
                alert("ERRO! Local com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.id_local) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.nome_instituicao) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.tipo_escola) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.tipo) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.nome) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.capacidade) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.longitude) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(local.latitude) + '</td>' +
                '<td>' +
                    '<a class="btn btn-primary btn-sm me-2" href="local_alterar.html?id=' + encodeURIComponent(local.id_local) + '">Alterar</a>' +
                    '<button class="btn btn-danger btn-sm" onclick="excluir(' + local.id_local + ')">Excluir</button>' +
                '</td>' +
            '</tr>';
        }
    }

    html += "</tbody></table>";
    document.getElementById("lista").innerHTML = html;
}

async function excluir(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja excluir este local? Esta acao nao pode ser desfeita.")
        : confirm("Tem certeza que deseja excluir este local? Esta acao nao pode ser desfeita.");

    if (!confirmado) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/locais/excluir.php?id=" + encodeURIComponent(id));

    if (resposta.status === "ok") {
        alert(CampusTrack.resposta.mensagem(resposta));
        window.location.reload();
    } else {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
    }
}
