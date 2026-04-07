document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    var url = new URLSearchParams(window.location.search);
    var id = url.get("id");
    buscarDados(id);
});

async function buscarDados(id) {
    const retornoInst = await fetch("../../php/instituicao_get.php");
    const respostaInst = await retornoInst.json();
    if (respostaInst.status != "ok") {
        alert("ERRO! " + respostaInst.mensagem);
        return;
    }

    var sel = document.getElementById("gerente-id_instituicao");
    sel.innerHTML = '<option value="">Selecione</option>';
    for (var j = 0; j < respostaInst.data.length; j++) {
        var ins = respostaInst.data[j];
        sel.innerHTML += "<option value='" + ins.id_instituicao + "'>" + ins.nome + "</option>";
    }

    if (!id) {
        alert("ERRO! ID não informado na URL.");
        return;
    }

    const retorno = await fetch("../../php/gerente_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        var reg = resposta.data[0];
        document.getElementById("gerente-id_usuario").value = reg.id_usuario;
        document.getElementById("gerente-nome").value = reg.nome || "";
        document.getElementById("gerente-email").value = reg.email || "";
        document.getElementById("gerente-senha").value = reg.senha || "";
        document.getElementById("gerente-id_instituicao").value = reg.id_instituicao;
        document.getElementById("gerente-escola").value = reg.escola || "";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-gerente").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_gerente();
});

async function alterar_gerente() {
    var id = document.getElementById("gerente-id_usuario").value;
    var nome = document.getElementById("gerente-nome").value;
    var email = document.getElementById("gerente-email").value;
    var senha = document.getElementById("gerente-senha").value;
    var id_instituicao = document.getElementById("gerente-id_instituicao").value;
    var escola = document.getElementById("gerente-escola").value;

    const gerente_alterado = new FormData();
    gerente_alterado.append("nome", nome);
    gerente_alterado.append("email", email);
    gerente_alterado.append("senha", senha);
    gerente_alterado.append("id_instituicao", id_instituicao);
    gerente_alterado.append("escola", escola);

    const retorno = await fetch("../../php/gerente_alterar.php?id=" + id, {
        method: "POST",
        body: gerente_alterado,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_gerente.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
