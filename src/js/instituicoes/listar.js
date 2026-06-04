document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarDados();

    document.getElementById("novo").addEventListener("click", function () {
        window.location.href = "instituicao_adicionar.html";
    });
});

async function carregarDados() {
    const resposta = await CampusTrack.api.json("../../php/instituicoes/get.php");

    if (resposta.status !== "ok") {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
        return;
    }

    const registros = resposta.data;

    if (!Array.isArray(registros)) {
        alert("ERRO! Lista de instituicoes invalida no retorno do servidor.");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>ID</th>' +
            '<th>Nome</th>' +
            '<th>Acoes</th>' +
        '</tr></thead><tbody>';

    if (registros.length === 0) {
        html += '<tr><td colspan="3" class="text-center text-muted">Nenhuma instituicao cadastrada.</td></tr>';
    } else {
        for (var i = 0; i < registros.length; i++) {
            var instituicao = registros[i];

            if (!instituicao.id_instituicao || !instituicao.nome) {
                alert("ERRO! Instituicao com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + CampusTrack.dom.escapeHtml(instituicao.id_instituicao) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(instituicao.nome) + '</td>' +
                '<td>' +
                    '<a class="btn btn-primary btn-sm me-2" href="instituicao_alterar.html?id=' + encodeURIComponent(instituicao.id_instituicao) + '">Alterar</a>' +
                    '<button class="btn btn-danger btn-sm" onclick="excluir(' + instituicao.id_instituicao + ')">Excluir</button>' +
                '</td>' +
            '</tr>';
        }
    }

    html += "</tbody></table>";
    document.getElementById("lista").innerHTML = html;
}

async function excluir(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja excluir esta instituicao? Esta acao nao pode ser desfeita e removera todos os registros associados.")
        : confirm("Tem certeza que deseja excluir esta instituicao? Esta acao nao pode ser desfeita e removera todos os registros associados.");

    if (!confirmado) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/instituicoes/excluir.php?id=" + encodeURIComponent(id));

    if (resposta.status === "ok") {
        alert(CampusTrack.resposta.mensagem(resposta));
        window.location.reload();
    } else {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
    }
}
