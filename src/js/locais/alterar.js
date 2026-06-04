document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    var url = new URLSearchParams(window.location.search);
    buscarDados(url.get("id"));

    document.getElementById("form-local").addEventListener("submit", function (event) {
        event.preventDefault();
        alterar_local(this);
    });
});

async function buscarDados(id) {
    const selectCarregado = await CampusTrack.select.carregar({
        url: "../../php/instituicoes/get_gerente.php",
        selectId: "local-id_instituicao",
        valor: "id_instituicao",
        texto: "nome",
        mensagemVazio: "Nenhuma instituicao disponivel para vincular ao local.",
    });

    if (!selectCarregado) {
        return;
    }

    const resposta = await CampusTrack.api.json("../../php/locais/get.php?id=" + encodeURIComponent(id || ""));

    if (resposta.status !== "ok") {
        alert("ERRO! " + resposta.mensagem);
        return;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        alert("ERRO! Local nao encontrado.");
        return;
    }

    preencherFormulario(resposta.data[0]);
}

function preencherFormulario(local) {
    document.getElementById("local-id_local").value = local.id_local;
    document.getElementById("local-id_instituicao").value = local.id_instituicao;
    document.getElementById("local-tipo_escola").value = local.tipo_escola;
    document.getElementById("local-tipo").value = local.tipo;
    document.getElementById("local-nome").value = local.nome;
    document.getElementById("local-capacidade").value = local.capacidade;
    document.getElementById("local-longitude").value = local.longitude;
    document.getElementById("local-latitude").value = local.latitude;
}

async function alterar_local(form) {
    var id = document.getElementById("local-id_local").value;
    const resposta = await CampusTrack.form.enviar(form, "../../php/locais/alterar.php?id=" + encodeURIComponent(id));
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_local.html";
    }
}
