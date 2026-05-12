document.addEventListener("DOMContentLoaded", () => {
});

document.getElementById("form-organizador").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_organizador();
});

function validarSenha(senha) {
    return senha.length >= 8 && /[A-Z]/.test(senha) && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
}

async function adicionar_organizador() {
    var nome = document.getElementById("organizador-nome").value.trim();
    var email = document.getElementById("organizador-email").value.trim();
    var senha = document.getElementById("organizador-senha").value.trim();

    if (nome === "" || email === "" || senha === "") {
        alert("ERRO! Todos os campos do organizador sao obrigatorios.");
        return;
    }

    if (!validarSenha(senha)) {
        alert("ERRO! A senha deve ter pelo menos 8 caracteres, 1 letra maiuscula, 1 numero e 1 simbolo.");
        return;
    }

    const novo_organizador = new FormData();
    novo_organizador.append("nome", nome);
    novo_organizador.append("email", email);
    novo_organizador.append("senha", senha);

    const retorno = await fetch("../../php/organizador_adicionar.php", {
        method: "POST",
        body: novo_organizador,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_organizador.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
        if (resposta.mensagem === "Acesso negado.") {
            window.location.href = "login.html?tipo=organizacao";
        }
    }
}
