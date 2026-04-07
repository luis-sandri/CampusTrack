document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarDados();
});

document.getElementById("novo").addEventListener("click", () => {
    window.location.href = "instituicao_adicionar.html";
});

async function carregarDados() {
    const retorno = await fetch("../../php/instituicao_get.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        const registros = resposta.data;

        var html = `<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>`;

        if (registros.length === 0) {
            html +=
                '<tr><td colspan="3" class="text-center text-muted">Nenhuma instituição cadastrada.</td></tr>';
        } else {
            for (var i = 0; i < registros.length; i++) {
                var objeto = registros[i];
                html += `<tr>
                <td>${objeto.id_instituicao}</td>
                <td>${objeto.nome}</td>
                <td>
                    <a class="btn btn-primary btn-sm me-2" href='instituicao_alterar.html?id=${objeto.id_instituicao}'>Alterar</a>
                    <button class="btn btn-danger btn-sm" onclick="excluir(${objeto.id_instituicao})">Excluir</button>
                </td>
            </tr>`;
            }
        }

        html += "</tbody></table>";
        document.getElementById("lista").innerHTML = html;
    } else {
        alert("Erro:" + resposta.mensagem);
    }
}

async function excluir(id) {
    const retorno = await fetch("../../php/instituicao_excluir.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert(resposta.mensagem);
        window.location.reload();
    } else {
        alert("ERRO: " + resposta.mensagem);
    }
}
