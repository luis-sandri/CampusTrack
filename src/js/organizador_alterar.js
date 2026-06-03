document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    var url = new URLSearchParams(window.location.search);
    buscarDados(url.get("id"));

    document.getElementById("form-organizador").addEventListener("submit", function (event) {
        event.preventDefault();
        alterar_organizador(this);
    });
});

async function buscarDados(id) {
    const resposta = await ctJson("../../php/organizador_get.php?id=" + encodeURIComponent(id || ""));

    if (resposta.status !== "ok") {
        alert("ERRO! " + resposta.mensagem);
        if (resposta.mensagem === "Acesso negado.") {
            window.location.href = "login.html?tipo=organizacao";
        }
        return;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        alert("ERRO! Organizador nao encontrado.");
        return;
    }

    var reg = resposta.data[0];
    document.getElementById("organizador-id_usuario").value = reg.id_usuario;
    document.getElementById("organizador-nome").value = reg.nome;
    document.getElementById("organizador-email").value = reg.email;
}

async function alterar_organizador(form) {
    var id = document.getElementById("organizador-id_usuario").value;
    const resposta = await ctEnviarFormulario(form, "../../php/organizador_alterar.php?id=" + encodeURIComponent(id));
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_organizador.html";
    }
}
