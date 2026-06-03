document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();

    document.getElementById("form-local").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_local(this);
    });
});

function carregarInstituicoes() {
    return ctCarregarSelect({
        url: "../../php/instituicao_get_gerente.php",
        selectId: "local-id_instituicao",
        valor: "id_instituicao",
        texto: "nome",
        mensagemVazio: "Nenhuma instituicao disponivel para vincular ao local.",
    });
}

async function adicionar_local(form) {
    const resposta = await ctEnviarFormulario(form, "../../php/local_adicionar.php");
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_local.html";
    }
}
