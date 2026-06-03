document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    document.getElementById("form-organizador").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_organizador(this);
    });
});

async function adicionar_organizador(form) {
    const resposta = await ctEnviarFormulario(form, "../../php/organizador_adicionar.php");
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_organizador.html";
        return;
    }

    if (resposta.mensagem === "Acesso negado.") {
        window.location.href = "login.html?tipo=organizacao";
    }
}
