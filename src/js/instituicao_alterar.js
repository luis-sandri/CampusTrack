document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    var url = new URLSearchParams(window.location.search);
    var id = url.get("id");
    buscarDados(id);
});

async function buscarDados(id) {
    if (!id) {
        alert("ERRO! ID não informado na URL.");
        return;
    }

    const retorno = await fetch("../../php/instituicao_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        var reg = resposta.data[0];
        document.getElementById("instituicao-id_instituicao").value = reg.id_instituicao;
        document.getElementById("instituicao-nome").value = reg.nome || "";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-instituicao").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_instituicao();
});

async function alterar_instituicao() {
    var id = document.getElementById("instituicao-id_instituicao").value;
    var nome = document.getElementById("instituicao-nome").value;

    const instituicao_alterada = new FormData();
    instituicao_alterada.append("nome", nome);

    const retorno = await fetch("../../php/instituicao_alterar.php?id=" + id, {
        method: "POST",
        body: instituicao_alterada,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        window.location.href = "gerenciar_instituicao.html";
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}
