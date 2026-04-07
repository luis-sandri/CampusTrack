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

    var sel = document.getElementById("local-id_instituicao");
    sel.innerHTML = '<option value="">Selecione</option>';
    for (var j = 0; j < respostaInst.data.length; j++) {
        var ins = respostaInst.data[j];
        sel.innerHTML += "<option value='" + ins.id_instituicao + "'>" + ins.nome + "</option>";
    }

    if (!id) {
        alert("ERRO! ID não informado na URL.");
        return;
    }

    const retorno = await fetch("../../php/local_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        var reg = resposta.data[0];
        document.getElementById("local-id_local").value = reg.id_local;
        document.getElementById("local-id_instituicao").value = reg.id_instituicao;
        document.getElementById("local-tipo").value = reg.tipo || "";
        document.getElementById("local-nome").value = reg.nome || "";
        document.getElementById("local-capacidade").value =
            reg.capacidade !== null && reg.capacidade !== undefined ? reg.capacidade : "";
        document.getElementById("local-longitude").value = reg.longitude || "";
        document.getElementById("local-latitude").value = reg.latitude || "";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-local").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_local();
});

async function alterar_local() {
    var id = document.getElementById("local-id_local").value;
    var id_instituicao = document.getElementById("local-id_instituicao").value;
    var tipo = document.getElementById("local-tipo").value;
    var nome = document.getElementById("local-nome").value;
    var capacidade = document.getElementById("local-capacidade").value;
    var longitude = document.getElementById("local-longitude").value;
    var latitude = document.getElementById("local-latitude").value;

    const local_alterado = new FormData();
    local_alterado.append("id_instituicao", id_instituicao);
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
