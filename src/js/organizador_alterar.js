document.addEventListener("DOMContentLoaded", () => {
    var url = new URLSearchParams(window.location.search);
    var id = url.get("id");
    buscarDados(id);
});

async function buscarDados(id) {
    if (!id || !/^\d+$/.test(id)) {
        alert("ERRO! ID nao informado ou invalido na URL.");
        return;
    }

    const retorno = await fetch("../../php/organizador_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Retorno do servidor invalido.");
            return;
        }

        var reg = resposta.data[0];

        if (!reg || !reg.id_usuario || !reg.nome || !reg.email || !reg.senha) {
            alert("ERRO! Dados do organizador incompletos no retorno do servidor.");
            return;
        }

        document.getElementById("organizador-id_usuario").value = reg.id_usuario;
        document.getElementById("organizador-nome").value = reg.nome;
        document.getElementById("organizador-email").value = reg.email;
        document.getElementById("organizador-senha").value = reg.senha;
    } else {
        alert("ERRO! " + resposta.mensagem);
        if (resposta.mensagem === "Acesso negado.") {
            window.location.href = "login.html?tipo=organizacao";
        }
    }
}

document.getElementById("form-organizador").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_organizador();
});

function validarSenha(senha) {
    return senha.length >= 8 && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
}

async function alterar_organizador() {
    var id = document.getElementById("organizador-id_usuario").value.trim();
    var nome = document.getElementById("organizador-nome").value.trim();
    var email = document.getElementById("organizador-email").value.trim();
    var senha = document.getElementById("organizador-senha").value.trim();

    if (id === "" || nome === "" || email === "" || senha === "") {
        alert("ERRO! Todos os campos do organizador sao obrigatorios.");
        return;
    }

    if (!/^\d+$/.test(id)) {
        alert("ERRO! ID precisa ser um numero valido.");
        return;
    }

    if (!validarSenha(senha)) {
        alert("ERRO! A senha deve ter pelo menos 8 caracteres, 1 numero e 1 simbolo.");
        return;
    }

    const organizador_alterado = new FormData();
    organizador_alterado.append("nome", nome);
    organizador_alterado.append("email", email);
    organizador_alterado.append("senha", senha);

    const retorno = await fetch("../../php/organizador_alterar.php?id=" + id, {
        method: "POST",
        body: organizador_alterado,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_organizador.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
