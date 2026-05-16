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

    if (!Array.isArray(respostaInst.data)) {
        alert("ERRO! Lista de instituições inválida no retorno do servidor.");
        return;
    }

    if (respostaInst.data.length === 0) {
        alert("ERRO! Nenhuma instituição disponível para vincular ao gerente.");
        return;
    }

    var sel = document.getElementById("gerente-id_instituicao");
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

    const retorno = await fetch("../../php/gerente_get.php?id=" + id);
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
            !reg.id_usuario ||
            !reg.nome ||
            !reg.email ||
            !reg.id_instituicao ||
            !reg.escola
        ) {
            alert("ERRO! Dados do gerente incompletos no retorno do servidor.");
            return;
        }

        document.getElementById("gerente-id_usuario").value = reg.id_usuario;
        document.getElementById("gerente-nome").value = reg.nome;
        document.getElementById("gerente-email").value = reg.email;
        document.getElementById("gerente-id_instituicao").value = reg.id_instituicao;
        document.getElementById("gerente-escola").value = reg.escola;
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-gerente").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_gerente();
});

async function alterar_gerente() {
    var id = document.getElementById("gerente-id_usuario").value.trim();
    var nome = document.getElementById("gerente-nome").value.trim();
    var email = document.getElementById("gerente-email").value.trim();
    var id_instituicao = document.getElementById("gerente-id_instituicao").value.trim();
    var escola = document.getElementById("gerente-escola").value.trim();

    if (id === "" || nome === "" || email === "" || id_instituicao === "" || escola === "") {
        alert("ERRO! Todos os campos do gerente são obrigatórios.");
        return;
    }

    if (!/^\d+$/.test(id) || !/^\d+$/.test(id_instituicao)) {
        alert("ERRO! ID e instituição precisam ser números válidos.");
        return;
    }

    const gerente_alterado = new FormData();
    gerente_alterado.append("nome", nome);
    gerente_alterado.append("email", email);
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
