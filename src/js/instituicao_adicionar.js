document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
});

document.getElementById("form-instituicao").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_instituicao();
});

async function adicionar_instituicao() {
    var nome = document.getElementById("instituicao-nome").value;

    const nova_instituicao = new FormData();
    nova_instituicao.append("nome", nome);

    const retorno = await fetch("../../php/instituicao_adicionar.php", {
        method: "POST",
        body: nova_instituicao,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_instituicao.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
