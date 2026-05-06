document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();
});

async function carregarInstituicoes() {
    const retorno = await fetch("../../php/instituicao_get_gerente.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Lista de instituições inválida no retorno do servidor.");
            return;
        }

        if (resposta.data.length === 0) {
            alert("ERRO! Nenhuma instituição disponível para vincular ao local.");
            return;
        }

        var sel = document.getElementById("local-id_instituicao");
        sel.innerHTML = '<option value="">Selecione</option>';
        for (var i = 0; i < resposta.data.length; i++) {
            var ins = resposta.data[i];

            if (!ins.id_instituicao || !ins.nome) {
                alert("ERRO! Instituição com dados incompletos no retorno do servidor.");
                return;
            }

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
    var id_instituicao = document.getElementById("local-id_instituicao").value.trim();
    var tipo_escola = document.getElementById("local-tipo_escola").value.trim();
    var tipo = document.getElementById("local-tipo").value.trim();
    var nome = document.getElementById("local-nome").value.trim();
    var capacidade = document.getElementById("local-capacidade").value.trim();
    var longitude = document.getElementById("local-longitude").value.trim();
    var latitude = document.getElementById("local-latitude").value.trim();

    if (
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

    if (!/^\d+$/.test(id_instituicao) || !/^\d+$/.test(capacidade)) {
        alert("ERRO! Instituição e capacidade precisam ser números válidos.");
        return;
    }

    const novo_local = new FormData();
    novo_local.append("id_instituicao", id_instituicao);
    novo_local.append("tipo_escola", tipo_escola);
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
