document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarInstituicoes();

    document.getElementById("evento-id_instituicao").addEventListener("change", function () {
        carregarLocais(this.value);
    });

    document.getElementById("form-evento").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_evento(this);
    });
});

async function carregarInstituicoes() {
    const resposta = await ctJson("../../php/instituicao_get.php");
    var selectInstituicao = document.getElementById("evento-id_instituicao");

    if (resposta.status !== "ok") {
        selectInstituicao.innerHTML = '<option value="">Selecione</option>';
        alert("ERRO! " + ctMensagem(resposta));
        return;
    }

    if (!Array.isArray(resposta.data)) {
        alert("ERRO! Lista de instituicoes invalida no retorno do servidor.");
        return;
    }

    selectInstituicao.innerHTML = '<option value="">Selecione</option>';
    for (var i = 0; i < resposta.data.length; i++) {
        var instituicao = resposta.data[i];
        selectInstituicao.innerHTML += "<option value='" + instituicao.id_instituicao + "'>" + instituicao.nome + "</option>";
    }
}

async function carregarLocais(id_instituicao) {
    var selectLocal = document.getElementById("evento-id_local");
    selectLocal.innerHTML = '<option value="">Selecione</option>';
    selectLocal.disabled = true;

    if (id_instituicao === "") {
        selectLocal.innerHTML = '<option value="">Selecione uma instituicao</option>';
        return;
    }

    const resposta = await ctJson("../../php/local_get.php?id_instituicao=" + encodeURIComponent(id_instituicao));

    if (resposta.status !== "ok") {
        alert("ERRO! " + ctMensagem(resposta));
        return;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        selectLocal.innerHTML = '<option value="">Nenhum local disponivel</option>';
        return;
    }

    for (var i = 0; i < resposta.data.length; i++) {
        var local = resposta.data[i];
        selectLocal.innerHTML += "<option value='" + local.id_local + "'>" + local.nome + "</option>";
    }

    selectLocal.disabled = false;
}

async function adicionar_evento(form) {
    const resposta = await ctEnviarFormulario(form, "../../php/evento_adicionar.php");
    ctAlertarResposta(resposta);

    if (resposta.status === "ok") {
        window.location.href = "organizador_dashboard.html";
    }
}
