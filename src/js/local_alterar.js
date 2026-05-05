document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    var url = new URLSearchParams(window.location.search);
    var id = url.get("id");
    buscarDados(id);
});

async function buscarDados(id) {
    const retornoInst = await fetch("../../php/instituicao_get_gerente.php");
    const respostaInst = await retornoInst.json();
    if (respostaInst.status != "ok") {
        alert("ERRO! " + respostaInst.mensagem);
        return;
    }

    if (!Array.isArray(respostaInst.data)) {
        alert("ERRO! Lista de instituições inválida no retorno do servidor.");
        return;
    }

    if (respostaInst.data.length === 0) {
        alert("ERRO! Nenhuma instituição disponível para vincular ao local.");
        return;
    }

    var sel = document.getElementById("local-id_instituicao");
    sel.innerHTML = '<option value="">Selecione</option>';
    for (var j = 0; j < respostaInst.data.length; j++) {
        var ins = respostaInst.data[j];

        if (!ins.id_instituicao || !ins.nome) {
            alert("ERRO! Instituição com dados incompletos no retorno do servidor.");
            return;
        }

        sel.innerHTML += "<option value='" + ins.id_instituicao + "'>" + ins.nome + "</option>";
    }

    if (!id || !/^\d+$/.test(id)) {
        alert("ERRO! ID não informado ou inválido na URL.");
        return;
    }

    const retorno = await fetch("../../php/local_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Retorno do servidor inválido.");
            return;
        }

        var reg = resposta.data[0];

        if (
            !reg ||
            !reg.id_local ||
            !reg.id_instituicao ||
            !reg.tipo_escola ||
            !reg.tipo ||
            !reg.nome ||
            reg.capacidade === null ||
            reg.capacidade === undefined ||
            !reg.longitude ||
            !reg.latitude
        ) {
            alert("ERRO! Dados do local incompletos no retorno do servidor.");
            return;
        }

        document.getElementById("local-id_local").value = reg.id_local;
        document.getElementById("local-id_instituicao").value = reg.id_instituicao;
        document.getElementById("local-tipo_escola").value = reg.tipo_escola;
        document.getElementById("local-tipo").value = reg.tipo;
        document.getElementById("local-nome").value = reg.nome;
        document.getElementById("local-capacidade").value = reg.capacidade;
        document.getElementById("local-longitude").value = reg.longitude;
        document.getElementById("local-latitude").value = reg.latitude;
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-local").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_local();
});

async function alterar_local() {
    var id = document.getElementById("local-id_local").value.trim();
    var id_instituicao = document.getElementById("local-id_instituicao").value.trim();
    var tipo_escola = document.getElementById("local-tipo_escola").value.trim();
    var tipo = document.getElementById("local-tipo").value.trim();
    var nome = document.getElementById("local-nome").value.trim();
    var capacidade = document.getElementById("local-capacidade").value.trim();
    var longitude = document.getElementById("local-longitude").value.trim();
    var latitude = document.getElementById("local-latitude").value.trim();

    if (
        id === "" ||
        id_instituicao === "" ||
        tipo_escola === "" ||
        tipo === "" ||
        nome === "" ||
        capacidade === "" ||
        longitude === "" ||
        latitude === ""
    ) {
        alert("ERRO! Todos os campos do local são obrigatórios.");
        return;
    }

    if (!/^\d+$/.test(id) || !/^\d+$/.test(id_instituicao) || !/^\d+$/.test(capacidade)) {
        alert("ERRO! ID, instituição e capacidade precisam ser números válidos.");
        return;
    }

    const local_alterado = new FormData();
    local_alterado.append("id_instituicao", id_instituicao);
    local_alterado.append("tipo_escola", tipo_escola);
    local_alterado.append("tipo", tipo);
    local_alterado.append("nome", nome);
    local_alterado.append("capacidade", capacidade);
    local_alterado.append("longitude", longitude);
    local_alterado.append("latitude", latitude);

    const retorno = await fetch("../../php/local_alterar.php?id=" + id, {
        method: "POST",
        body: local_alterado,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_local.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
