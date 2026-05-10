document.addEventListener("DOMContentLoaded", () => {
    carregarDados();
});

document.getElementById("novo").addEventListener("click", () => {
    window.location.href = "organizador_adicionar.html";
});

async function carregarDados() {
    const retorno = await fetch("../../php/organizador_get.php");
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        const registros = resposta.data;

        if (!Array.isArray(registros)) {
            alert("ERRO! Lista de organizadores invalida no retorno do servidor.");
            return;
        }

        var html = `<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Acoes</th>
        </tr>
    </thead>
    <tbody>`;

        if (registros.length === 0) {
            html +=
                '<tr><td colspan="4" class="text-center text-muted">Nenhum organizador cadastrado.</td></tr>';
        } else {
            for (var i = 0; i < registros.length; i++) {
                var objeto = registros[i];

                if (!objeto.id_usuario || !objeto.id_organizador || !objeto.nome || !objeto.email) {
                    alert("ERRO! Organizador com dados incompletos no retorno do servidor.");
                    return;
                }

                html += `<tr>
                <td>${objeto.id_organizador}</td>
                <td>${objeto.nome}</td>
                <td>${objeto.email}</td>
                <td>
                    <a class="btn btn-primary btn-sm me-2" href='organizador_alterar.html?id=${objeto.id_usuario}'>Alterar</a>
                    <button class="btn btn-danger btn-sm" onclick="excluir(${objeto.id_usuario})">Excluir</button>
                </td>
            </tr>`;
            }
        }

        html += "</tbody></table>";
        document.getElementById("lista").innerHTML = html;
    } else {
        alert("Erro:" + resposta.mensagem);
        if (resposta.mensagem === "Acesso negado.") {
            window.location.href = "login.html?tipo=organizacao";
        }
    }
}

async function excluir(id) {
    const retorno = await fetch("../../php/organizador_excluir.php?id=" + id);
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        alert(resposta.mensagem);
        window.location.reload();
    } else {
        alert("ERRO: " + resposta.mensagem);
    }
}

