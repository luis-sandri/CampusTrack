document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    var url = new URLSearchParams(window.location.search);
    buscarDados(url.get("id"));

    document.getElementById("form-gerente").addEventListener("submit", function (event) {
        event.preventDefault();
        alterar_gerente(this);
    });
});

async function buscarDados(id) {
    const carregouInstituicoes = await CampusTrack.select.carregar({
        url: "../../php/instituicoes/get.php",
        selectId: "gerente-id_instituicao",
        valor: "id_instituicao",
        texto: "nome",
        mensagemVazio: "Nenhuma instituicao disponivel para vincular ao gerente.",
        placeholder: "Selecione",
    });

    if (!carregouInstituicoes) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/gerentes/get.php?id=" + encodeURIComponent(id || ""));

    if (resposta.status !== "ok") {
        alert("ERRO! " + resposta.mensagem);
        return;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        alert("ERRO! Gerente nao encontrado.");
        return;
    }

    var reg = resposta.data[0];
    document.getElementById("gerente-id_usuario").value = reg.id_usuario;
    document.getElementById("gerente-nome").value = reg.nome;
    document.getElementById("gerente-email").value = reg.email;
    document.getElementById("gerente-id_instituicao").value = reg.id_instituicao;
    document.getElementById("gerente-escola").value = reg.escola;
}

async function alterar_gerente(form) {
    var id = document.getElementById("gerente-id_usuario").value;
    const resposta = await CampusTrack.form.enviar(form, "../../php/gerentes/alterar.php?id=" + encodeURIComponent(id));
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_gerente.html";
    }
}
