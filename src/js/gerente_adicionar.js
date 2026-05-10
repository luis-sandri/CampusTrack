document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarInstituicoes();
});

async function carregarInstituicoes() {
    const retorno = await fetch("../../php/instituicao_get.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Lista de instituições inválida no retorno do servidor.");
            return;
        }

        if (resposta.data.length === 0) {
            alert("ERRO! Nenhuma instituição disponível para vincular ao gerente.");
            return;
        }

        var sel = document.getElementById("gerente-id_instituicao");
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

document.getElementById("form-gerente").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_gerente();
});

function validarSenha(senha) {
    return senha.length >= 8 && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
}

async function adicionar_gerente() {
    var nome = document.getElementById("gerente-nome").value.trim();
    var email = document.getElementById("gerente-email").value.trim();
    var senha = document.getElementById("gerente-senha").value.trim();
    var id_instituicao = document.getElementById("gerente-id_instituicao").value.trim();
    var escola = document.getElementById("gerente-escola").value.trim();

    if (nome === "" || email === "" || senha === "" || id_instituicao === "" || escola === "") {
        alert("ERRO! Todos os campos do gerente são obrigatórios.");
        return;
    }

    if (!/^\d+$/.test(id_instituicao)) {
        alert("ERRO! Instituição inválida.");
        return;
    }

    if (!validarSenha(senha)) {
        alert("ERRO! A senha deve ter pelo menos 8 caracteres, 1 numero e 1 simbolo.");
        return;
    }

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
