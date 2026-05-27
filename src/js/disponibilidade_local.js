document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
});

document.getElementById("formBusca").addEventListener("submit", async (e) => {
    e.preventDefault();

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
        msgAlert.innerText = "Data ou horário inválidos. Informe um período futuro para realizar a consulta.";
        msgAlert.classList.remove("d-none");
        return;
    }

    try {
        const retorno = await fetch(`../../php/disponibilidade_local_get.php?data=${encodeURIComponent(dataInput)}`);
        const resposta = await retorno.json();

        if (resposta.status !== "ok") {
            msgAlert.innerText = resposta.mensagem || "Não foi possível consultar a disponibilidade.";
            msgAlert.classList.remove("d-none");
            return;
        }

        const registros = Array.isArray(resposta.data) ? resposta.data : [];

        if (registros.length === 0) {
            msgAlert.innerText = "Nenhum espaço disponível para o período selecionado";
            msgAlert.classList.remove("d-none");
            return;
        }

        let html = `<table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Capacidade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>`;

        registros.forEach((objeto) => {
            html += `<tr>
                <td>${textoSeguroDisponibilidade(objeto.nome_evento)}</td>
                <td>${textoSeguroDisponibilidade(objeto.data_formatada || objeto.data)}</td>
                <td>${textoSeguroDisponibilidade(objeto.nome_local)}</td>
                <td>${textoSeguroDisponibilidade(objeto.tipo)}</td>
                <td>${textoSeguroDisponibilidade(objeto.capacidade)}</td>
                <td><span class="badge bg-danger">${textoSeguroDisponibilidade(objeto.status_disponibilidade || "Ocupado")}</span></td>
            </tr>`;
        });

        html += "</tbody></table>";
        listaResultados.innerHTML = html;
        cardResultados.style.display = "block";
    } catch (erro) {
        msgAlert.innerText = "Erro de conexão ao consultar a disponibilidade.";
        msgAlert.classList.remove("d-none");
    }
});

function textoSeguroDisponibilidade(valor) {
    return String(valor ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
