document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    var url = new URLSearchParams(window.location.search);
    buscarDados(url.get("id"));

    document.getElementById("form-instituicao").addEventListener("submit", function (event) {
        event.preventDefault();
        alterar_instituicao(this);
    });
});

async function buscarDados(id) {
    const resposta = await ctJson("../../php/instituicao_get.php?id=" + encodeURIComponent(id || ""));

    if (resposta.status !== "ok") {
        alert("ERRO! " + resposta.mensagem);
        return;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        alert("ERRO! Instituicao nao encontrada.");
        return;
    }

    var reg = resposta.data[0];
    document.getElementById("instituicao-id_instituicao").value = reg.id_instituicao;
    document.getElementById("instituicao-nome").value = reg.nome;
}

async function alterar_instituicao(form) {
    var id = document.getElementById("instituicao-id_instituicao").value;
    const resposta = await ctEnviarFormulario(form, "../../php/instituicao_alterar.php?id=" + encodeURIComponent(id));
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_instituicao.html";
    }
}
