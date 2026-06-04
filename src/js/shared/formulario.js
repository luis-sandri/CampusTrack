(function (window) {
    var CampusTrack = window.CampusTrack || {};

    function respostaErro(mensagem) {
        return {
            status: "not ok",
            mensagem: mensagem,
            data: [],
        };
    }

    async function json(url, options) {
        try {
            const retorno = await fetch(url, options || {});
            const texto = await retorno.text();

            if (texto.trim() === "") {
                return respostaErro("Servidor respondeu vazio.");
            }

            try {
                return JSON.parse(texto);
            } catch (erro) {
                return respostaErro("Servidor retornou uma resposta invalida.");
            }
        } catch (erro) {
            return respostaErro("Nao foi possivel conectar ao servidor.");
        }
    }

    async function enviarFormulario(form, url) {
        return json(url, {
            method: "POST",
            body: new FormData(form),
        });
    }

    function mensagem(resposta) {
        if (!resposta || typeof resposta.mensagem !== "string" || resposta.mensagem.trim() === "") {
            return "Resposta sem mensagem do servidor.";
        }

        return resposta.mensagem;
    }

    function alertar(resposta) {
        var prefixo = resposta.status === "ok" ? "Sucesso! " : "ERRO! ";
        alert(prefixo + mensagem(resposta));
    }

    function escapeHtml(valor) {
        return String(valor === null || valor === undefined ? "" : valor)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    async function carregarSelect(config) {
        const resposta = await json(config.url);
        var select = document.getElementById(config.selectId);

        if (!select) {
            alert("ERRO! Campo de selecao nao encontrado.");
            return false;
        }

        if (resposta.status !== "ok") {
            alert("ERRO! " + mensagem(resposta));
            return false;
        }

        if (!Array.isArray(resposta.data) || resposta.data.length === 0) {
            alert("ERRO! " + config.mensagemVazio);
            return false;
        }

        select.innerHTML = "";

        var optionInicial = document.createElement("option");
        optionInicial.value = "";
        optionInicial.textContent = config.placeholder || "Selecione";
        select.appendChild(optionInicial);

        for (var i = 0; i < resposta.data.length; i++) {
            var item = resposta.data[i];

            if (!item || item[config.valor] === undefined || item[config.texto] === undefined) {
                alert("ERRO! Lista carregada com dados incompletos.");
                return false;
            }

            var option = document.createElement("option");
            option.value = item[config.valor];
            option.textContent = item[config.texto];
            select.appendChild(option);
        }

        return true;
    }

    CampusTrack.api = {
        json: json,
    };

    CampusTrack.form = {
        enviar: enviarFormulario,
    };

    CampusTrack.resposta = {
        alertar: alertar,
        erro: respostaErro,
        mensagem: mensagem,
    };

    CampusTrack.dom = {
        escapeHtml: escapeHtml,
    };

    CampusTrack.select = {
        carregar: carregarSelect,
    };

    window.CampusTrack = CampusTrack;
})(window);
