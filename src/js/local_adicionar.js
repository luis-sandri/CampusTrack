document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();
});

async function carregarInstituicoes() {
    const retorno = await fetch("../../php/instituicao_get_gerente.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        var sel = document.getElementById("local-id_instituicao");
        sel.innerHTML = '<option value="">Selecione</option>';
        for (var i = 0; i < resposta.data.length; i++) {
            var ins = resposta.data[i];
            sel.innerHTML += "<option value='" + ins.id_instituicao + "'>" + ins.nome + "</option>";
        }
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-local").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_local();
});

async function adicionar_local() {
    var id_instituicao = document.getElementById("local-id_instituicao").value;
    var tipo = document.getElementById("local-tipo").value;
    var nome = document.getElementById("local-nome").value;
    var capacidade = document.getElementById("local-capacidade").value;
    var longitude = document.getElementById("local-longitude").value;
    var latitude = document.getElementById("local-latitude").value;

    const novo_local = new FormData();
    novo_local.append("id_instituicao", id_instituicao);
    novo_local.append("tipo", tipo);
    novo_local.append("nome", nome);
    novo_local.append("capacidade", capacidade);
    novo_local.append("longitude", longitude);
    novo_local.append("latitude", latitude);

    const retorno = await fetch("../../php/local_adicionar.php", {
        method: "POST",
        body: novo_local,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_local.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
