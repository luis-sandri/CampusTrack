document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();

    document.getElementById("form-gerente").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_gerente(this);
    });
});

async function carregarInstituicoes() {
    return CampusTrack.select.carregar({
        url: "../../php/instituicoes/get.php",
        selectId: "gerente-id_instituicao",
        valor: "id_instituicao",
        texto: "nome",
        mensagemVazio: "Nenhuma instituicao disponivel para vincular ao gerente.",
        placeholder: "Selecione",
    });
}

async function adicionar_gerente(form) {
    const resposta = await CampusTrack.form.enviar(form, "../../php/gerentes/adicionar.php");
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_gerente.html";
    }
}
