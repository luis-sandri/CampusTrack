document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();

    document.getElementById("form-local").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_local(this);
    });
});

function carregarInstituicoes() {
    return CampusTrack.select.carregar({
        url: "../../php/instituicoes/get_gerente.php",
        selectId: "local-id_instituicao",
        valor: "id_instituicao",
        texto: "nome",
        mensagemVazio: "Nenhuma instituicao disponivel para vincular ao local.",
    });
}

async function adicionar_local(form) {
    const resposta = await CampusTrack.form.enviar(form, "../../php/locais/adicionar.php");
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_local.html";
    }
}
