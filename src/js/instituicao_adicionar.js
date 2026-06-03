document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    document.getElementById("form-instituicao").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_instituicao(this);
    });
});

async function adicionar_instituicao(form) {
    const resposta = await ctEnviarFormulario(form, "../../php/instituicao_adicionar.php");
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_instituicao.html";
    }
}
