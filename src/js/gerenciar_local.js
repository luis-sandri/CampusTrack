document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarDados();
});

document.getElementById("novo").addEventListener("click", () => {
    window.location.href = "local_adicionar.html";
});

async function carregarDados() {
    const retorno = await fetch("../../php/local_get.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        const registros = resposta.data;

        if (!Array.isArray(registros)) {
            alert("ERRO! Lista de locais inválida no retorno do servidor.");
            return;
        }

        var html = `<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Instituição</th>
            <th>Tipo escola</th>
            <th>Tipo</th>
            <th>Nome</th>
            <th>Cap.</th>
            <th>Long.</th>
            <th>Lat.</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>`;

        if (registros.length === 0) {
            html +=
                '<tr><td colspan="9" class="text-center text-muted">Nenhum local cadastrado.</td></tr>';
        } else {
            for (var i = 0; i < registros.length; i++) {
                var objeto = registros[i];

                if (
                    !objeto.id_local ||
                    !objeto.id_instituicao ||
                    !objeto.nome_instituicao ||
                    !objeto.tipo_escola ||
                    !objeto.tipo ||
                    !objeto.nome ||
                    objeto.capacidade === null ||
                    objeto.capacidade === undefined ||
                    !objeto.longitude ||
                    !objeto.latitude
                ) {
                    alert("ERRO! Local com dados incompletos no retorno do servidor.");
                    return;
                }

                html += `<tr>
                <td>${objeto.id_local}</td>
                <td>${objeto.nome_instituicao}</td>
                <td>${objeto.tipo_escola}</td>
                <td>${objeto.tipo}</td>
                <td>${objeto.nome}</td>
                <td>${objeto.capacidade}</td>
                <td>${objeto.longitude}</td>
                <td>${objeto.latitude}</td>
                <td>
                    <a class="btn btn-primary btn-sm me-2" href='local_alterar.html?id=${objeto.id_local}'>Alterar</a>
                    <button class="btn btn-danger btn-sm" onclick="excluir(${objeto.id_local})">Excluir</button>
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
    const retorno = await fetch("../../php/local_excluir.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert(resposta.mensagem);
        window.location.reload();
    } else {
        alert("ERRO: " + resposta.mensagem);
    }
}
