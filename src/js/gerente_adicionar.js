document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();
});

async function carregarInstituicoes() {
    const retorno = await fetch("../../php/instituicao_get.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        var sel = document.getElementById("gerente-id_instituicao");
        sel.innerHTML = '<option value="">Selecione</option>';
        for (var i = 0; i < resposta.data.length; i++) {
            var ins = resposta.data[i];
            sel.innerHTML += "<option value='" + ins.id_instituicao + "'>" + ins.nome + "</option>";
        }
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-gerente").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_gerente();
});

async function adicionar_gerente() {
    var nome = document.getElementById("gerente-nome").value;
    var email = document.getElementById("gerente-email").value;
    var senha = document.getElementById("gerente-senha").value;
    var id_instituicao = document.getElementById("gerente-id_instituicao").value;
    var escola = document.getElementById("gerente-escola").value;

    const novo_gerente = new FormData();
    novo_gerente.append("nome", nome);
    novo_gerente.append("email", email);
    novo_gerente.append("senha", senha);
    novo_gerente.append("id_instituicao", id_instituicao);
    novo_gerente.append("escola", escola);

    const retorno = await fetch("../../php/gerente_adicionar.php", {
        method: "POST",
        body: novo_gerente,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_gerente.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
