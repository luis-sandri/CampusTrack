document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();

    document.getElementById("formBusca").addEventListener("submit", consultarDisponibilidade);
});

async function consultarDisponibilidade(event) {
    event.preventDefault();

    const dataInput = document.getElementById("data").value;
    const msgAlert = document.getElementById("mensagem");
    const cardResultados = document.getElementById("cardResultados");
    const listaResultados = document.getElementById("lista");

    msgAlert.classList.add("d-none");
    cardResultados.style.display = "none";
    listaResultados.innerHTML = "";

    if (!dataInput) {
        return;
    }

    const dataSelecionada = new Date(dataInput);
    const dataAtual = new Date();

    if (dataSelecionada <= dataAtual) {
        msgAlert.innerText = "Data ou horario invalidos. Informe um periodo futuro para realizar a consulta.";
        msgAlert.classList.remove("d-none");
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/disponibilidades/local_get.php?data=" + encodeURIComponent(dataInput));

    if (resposta.status !== "ok") {
        msgAlert.innerText = CampusTrack.resposta.mensagem(resposta);
        msgAlert.classList.remove("d-none");
        return;
    }

    const registros = Array.isArray(resposta.data) ? resposta.data : [];

    if (registros.length === 0) {
        msgAlert.innerText = CampusTrack.resposta.mensagem(resposta);
        msgAlert.classList.remove("d-none");
        return;
    }

    var html = '<table class="table table-striped align-middle">' +
        '<thead><tr>' +
            '<th>Evento</th>' +
            '<th>Data</th>' +
            '<th>Local</th>' +
            '<th>Tipo</th>' +
            '<th>Capacidade</th>' +
            '<th>Status</th>' +
        '</tr></thead><tbody>';

    for (var i = 0; i < registros.length; i++) {
        var item = registros[i];
        html += '<tr>' +
            '<td>' + CampusTrack.dom.escapeHtml(item.nome_evento) + '</td>' +
            '<td>' + CampusTrack.dom.escapeHtml(item.data_formatada || item.data) + '</td>' +
            '<td>' + CampusTrack.dom.escapeHtml(item.nome_local) + '</td>' +
            '<td>' + CampusTrack.dom.escapeHtml(item.tipo) + '</td>' +
            '<td>' + CampusTrack.dom.escapeHtml(item.capacidade) + '</td>' +
            '<td><span class="badge bg-danger">' + CampusTrack.dom.escapeHtml(item.status_disponibilidade || "Ocupado") + '</span></td>' +
        '</tr>';
    }

    html += "</tbody></table>";
    listaResultados.innerHTML = html;
    cardResultados.style.display = "block";
}
