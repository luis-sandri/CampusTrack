async function ctJson(url, options) {
    try {
        const retorno = await fetch(url, options || {});
        const texto = await retorno.text();

        if (texto.trim() === "") {
            return ctRespostaErro("Servidor respondeu vazio.");
        }

        try {
            return JSON.parse(texto);
        } catch (erro) {
            return ctRespostaErro("Servidor retornou uma resposta invalida.");
        }
    } catch (erro) {
        return ctRespostaErro("Nao foi possivel conectar ao servidor.");
    }
}

async function ctEnviarFormulario(form, url) {
    return ctJson(url, {
        method: "POST",
        body: new FormData(form),
    });
}

function ctAlertarResposta(resposta) {
    var prefixo = resposta.status === "ok" ? "Sucesso! " : "ERRO! ";
    alert(prefixo + ctMensagem(resposta));
}

function ctRespostaErro(mensagem) {
    return {
        status: "not ok",
        mensagem: mensagem,
        data: [],
    };
}

function ctMensagem(resposta) {
    if (!resposta || typeof resposta.mensagem !== "string" || resposta.mensagem.trim() === "") {
        return "Resposta sem mensagem do servidor.";
    }

    return resposta.mensagem;
}

async function ctCarregarSelect(config) {
    const resposta = await ctJson(config.url);
    var select = document.getElementById(config.selectId);

    if (!select) {
        alert("ERRO! Campo de selecao nao encontrado.");
        return false;
    }

    if (resposta.status !== "ok") {
        alert("ERRO! " + ctMensagem(resposta));
        return false;
    }

    if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
        alert("ERRO! " + config.mensagemVazio);
        return false;
    }

    select.innerHTML = '<option value="">' + (config.placeholder || "Selecione") + "</option>";
    for (var i = 0; i < resposta.data.length; i++) {
        var item = resposta.data[i];

        if (!item || item[config.valor] === undefined || item[config.texto] === undefined) {
            alert("ERRO! Lista carregada com dados incompletos.");
            return false;
        }

        select.innerHTML += "<option value='" + item[config.valor] + "'>" + item[config.texto] + "</option>";
    }

    return true;
}
