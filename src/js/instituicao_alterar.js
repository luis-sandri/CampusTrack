document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    var url = new URLSearchParams(window.location.search);
    var id = url.get("id");
    buscarDados(id);
});

async function buscarDados(id) {
    if (!id || !/^\d+$/.test(id)) {
        alert("ERRO! ID não informado ou inválido na URL.");
        return;
    }

    const retorno = await fetch("../../php/instituicao_get.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert("Sucesso! " + resposta.mensagem);
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Retorno do servidor inválido.");
            return;
        }

        var reg = resposta.data[0];

        if (!reg || !reg.id_instituicao || !reg.nome) {
            alert("ERRO! Dados da instituição incompletos no retorno do servidor.");
            return;
        }

        document.getElementById("instituicao-id_instituicao").value = reg.id_instituicao;
        document.getElementById("instituicao-nome").value = reg.nome;
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.getElementById("form-instituicao").addEventListener("submit", function (event) {
    event.preventDefault();
    alterar_instituicao();
});

async function alterar_instituicao() {
    var id = document.getElementById("instituicao-id_instituicao").value.trim();
    var nome = document.getElementById("instituicao-nome").value.trim();

    if (id === "" || nome === "") {
        alert("ERRO! ID e nome da instituição são obrigatórios.");
        return;
    }

    if (!/^\d+$/.test(id)) {
        alert("ERRO! ID da instituição inválido.");
        return;
    }

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
