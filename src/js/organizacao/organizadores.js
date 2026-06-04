document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarDados();

    document.getElementById("novo").addEventListener("click", function () {
        window.location.href = "organizador_adicionar.html";
    });
});

async function carregarDados() {
    const resposta = await CampusTrack.api.json("../../php/organizadores/get.php");

    if (resposta.status !== "ok") {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
        if (CampusTrack.resposta.mensagem(resposta) === "Acesso negado.") {
            window.location.href = "login.html";
        }
        return;
    }

    const registros = resposta.data;

    if (!Array.isArray(registros)) {
        alert("ERRO! Lista de organizadores invalida no retorno do servidor.");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>ID</th>' +
            '<th>Nome</th>' +
            '<th>E-mail</th>' +
            '<th>Acoes</th>' +
        '</tr></thead><tbody>';

    if (registros.length === 0) {
        html += '<tr><td colspan="4" class="text-center text-muted">Nenhum organizador cadastrado.</td></tr>';
    } else {
        for (var i = 0; i < registros.length; i++) {
            var organizador = registros[i];

            if (!organizador.id_usuario || !organizador.id_organizador || !organizador.nome || !organizador.email) {
                alert("ERRO! Organizador com dados incompletos no retorno do servidor.");
                return;
            }

            html += '<tr>' +
                '<td>' + CampusTrack.dom.escapeHtml(organizador.id_organizador) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(organizador.nome) + '</td>' +
                '<td>' + CampusTrack.dom.escapeHtml(organizador.email) + '</td>' +
                '<td>' +
                    '<a class="btn btn-primary btn-sm me-2" href="organizador_alterar.html?id=' + encodeURIComponent(organizador.id_usuario) + '">Alterar</a>' +
                    '<button class="btn btn-danger btn-sm" onclick="excluir(' + organizador.id_usuario + ')">Excluir</button>' +
                '</td>' +
            '</tr>';
        }
    }

    html += "</tbody></table>";
    document.getElementById("lista").innerHTML = html;
}

async function excluir(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Tem certeza que deseja excluir este organizador? Esta acao nao pode ser desfeita.")
        : confirm("Tem certeza que deseja excluir este organizador? Esta acao nao pode ser desfeita.");

    if (!confirmado) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/organizadores/excluir.php?id=" + encodeURIComponent(id));

    if (resposta.status === "ok") {
        alert(CampusTrack.resposta.mensagem(resposta));
        window.location.reload();
    } else {
        alert("ERRO! " + CampusTrack.resposta.mensagem(resposta));
    }
}
