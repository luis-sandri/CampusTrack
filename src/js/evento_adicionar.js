document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    carregarInstituicoes();
});

document.getElementById("evento-id_instituicao").addEventListener("change", function () {
    carregarLocais(this.value);
});

document.getElementById("form-evento").addEventListener("submit", function (event) {
    event.preventDefault();
    adicionar_evento();
});

async function carregarInstituicoes() {
    const retorno = await fetch("../../php/instituicao_get.php");
    const resposta = await retorno.json();

    var selectInstituicao = document.getElementById("evento-id_instituicao");

    if (resposta.status == "ok") {
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Lista de instituicoes invalida no retorno do servidor.");
            return;
        }

        selectInstituicao.innerHTML = '<option value="">Selecione</option>';

        for (var i = 0; i < resposta.data.length; i++) {
            var instituicao = resposta.data[i];

            if (!instituicao.id_instituicao || !instituicao.nome) {
                alert("ERRO! Instituicao com dados incompletos no retorno do servidor.");
                return;
            }

            selectInstituicao.innerHTML += "<option value='" + instituicao.id_instituicao + "'>" + instituicao.nome + "</option>";
        }
    } else {
        selectInstituicao.innerHTML = '<option value="">Selecione</option>';
        alert("ERRO! " + resposta.mensagem);
    }
}

async function carregarLocais(id_instituicao) {
    var selectLocal = document.getElementById("evento-id_local");
    selectLocal.innerHTML = '<option value="">Selecione</option>';
    selectLocal.disabled = true;

    if (id_instituicao === "") {
        selectLocal.innerHTML = '<option value="">Selecione uma instituicao</option>';
        return;
    }

    const retorno = await fetch("../../php/local_get.php?id_instituicao=" + encodeURIComponent(id_instituicao));
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        if (!Array.isArray(resposta.data)) {
            alert("ERRO! Lista de locais invalida no retorno do servidor.");
            return;
        }

        if (resposta.data.length === 0) {
            selectLocal.innerHTML = '<option value="">Nenhum local disponivel</option>';
            return;
        }

        for (var i = 0; i < resposta.data.length; i++) {
            var local = resposta.data[i];

            if (!local.id_local || !local.nome) {
                alert("ERRO! Local com dados incompletos no retorno do servidor.");
                return;
            }

            selectLocal.innerHTML += "<option value='" + local.id_local + "'>" + local.nome + "</option>";
        }

        selectLocal.disabled = false;
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

async function adicionar_evento() {
    var nome = document.getElementById("evento-nome").value.trim();
    var id_instituicao = document.getElementById("evento-id_instituicao").value.trim();
    var id_local = document.getElementById("evento-id_local").value.trim();
    var data = document.getElementById("evento-data").value.trim();

    if (nome === "" || id_instituicao === "" || id_local === "" || data === "") {
        alert("preencha toddos os campos");
        return;
    }

    const dataSelecionada = new Date(data);
    const dataAtual = new Date();

    if (dataSelecionada <= dataAtual) {
        window.__alertaOriginal("A data e horário do evento devem ser no futuro.");
        return;
    }

    const novo_evento = new FormData();
    novo_evento.append("nome", nome);
    novo_evento.append("id_instituicao", id_instituicao);
    novo_evento.append("id_local", id_local);
    novo_evento.append("data", data);

    const retorno = await fetch("../../php/evento_adicionar.php", {
        method: "POST",
        body: novo_evento,
    });
    const resposta = await retorno.json();

    if (resposta.status == "ok") {
        window.__alertaOriginal("Sucesso! " + resposta.mensagem);
        window.location.href = "organizador_dashboard.html";
    } else {
        window.__alertaOriginal(resposta.mensagem);
    }
}
