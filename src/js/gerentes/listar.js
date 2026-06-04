document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarDados();

    document.getElementById("novo").addEventListener("click", function () {
        window.location.href = "gerente_adicionar.html";
    });
});

async function carregarDados() {
    const resposta = await CampusTrack.api.json("../../php/gerentes/get.php");

    if (resposta.status !== "ok") {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
        return;
    }

    const registros = resposta.data;

    if (!Array.isArray(registros)) {
        alert("ERRO! Lista de gerentes invalida no retorno do servidor.");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>ID</th>' +
            '<th>Nome</th>' +
            '<th>E-mail</th>' +
            '<th>Instituicao</th>' +
            '<th>Escola</th>' +
            '<th>Acoes</th>' +
        '</tr></thead><tbody>';

    if (registros.length === 0) {
        html += '<tr><td colspan="6" class="text-center text-muted">Nenhum gerente cadastrado.</td></tr>';
    } else {
        for (var i = 0; i < registros.length; i++) {
            var gerente = registros[i];

            if (!gerente.id_usuario || !gerente.nome || !gerente.email || !gerente.id_instituicao || !gerente.nome_instituicao || !gerente.escola) {
                alert("ERRO! Gerente com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + CampusTrack.dom.escapeHtml(gerente.id_usuario) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(gerente.nome) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(gerente.email) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(gerente.nome_instituicao) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(gerente.escola) + '</td>' +
                '<td>' +
                    '<a class="btn btn-primary btn-sm me-2" href="gerente_alterar.html?id=' + encodeURIComponent(gerente.id_usuario) + '">Alterar</a>' +
                    '<button class="btn btn-danger btn-sm" onclick="excluir(' + gerente.id_usuario + ')">Excluir</button>' +
                '</td>' +
            '</tr>';
        }
    }

    html += "</tbody></table>";
    document.getElementById("lista").innerHTML = html;
}

async function excluir(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja excluir este gerente? Esta acao nao pode ser desfeita.")
        : confirm("Tem certeza que deseja excluir este gerente? Esta acao nao pode ser desfeita.");

    if (!confirmado) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/gerentes/excluir.php?id=" + encodeURIComponent(id));

    if (resposta.status === "ok") {
        alert(CampusTrack.resposta.mensagem(resposta));
        window.location.reload();
    } else {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
    }
}
