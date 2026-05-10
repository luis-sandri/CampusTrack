document.addEventListener("DOMContentLoaded", function () {
    var formCadastro = document.getElementById("form-organizacao-cadastro");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputNome = document.getElementById("organizacao-nome");
    var inputCnpj = document.getElementById("organizacao-cnpj");
    var inputSenha = document.getElementById("organizacao-senha");

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    function limparCnpj(cnpj) {
        return cnpj.replace(/\D/g, "");
    }

    function validarCnpj(cnpj) {
        if (!/^\d{14}$/.test(cnpj) || /^(\d)\1{13}$/.test(cnpj)) {
            return false;
        }

        var pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        var pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        var soma = 0;

        for (var i = 0; i < 12; i++) {
            soma += parseInt(cnpj.charAt(i), 10) * pesos1[i];
        }

        var digito = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (parseInt(cnpj.charAt(12), 10) !== digito) {
            return false;
        }

        soma = 0;
        for (var j = 0; j < 13; j++) {
            soma += parseInt(cnpj.charAt(j), 10) * pesos2[j];
        }

        digito = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        return parseInt(cnpj.charAt(13), 10) === digito;
    }

    function validarSenha(senha) {
        return senha.length >= 8 && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
    }

    formCadastro.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var nomeValor = inputNome.value.trim();
        var cnpjValor = limparCnpj(inputCnpj.value.trim());
        var senhaValor = inputSenha.value.trim();

        if (nomeValor === "" || cnpjValor === "" || senhaValor === "") {
            mostrarAlerta("Nome, CNPJ e senha sao obrigatorios.", "danger");
            return;
        }

        if (!validarCnpj(cnpjValor)) {
            mostrarAlerta("CNPJ invalido.", "danger");
            return;
        }

        if (!validarSenha(senhaValor)) {
            mostrarAlerta("A senha deve ter pelo menos 8 caracteres, 1 numero e 1 simbolo.", "danger");
            return;
        }

        var btnSubmit = document.getElementById("btn-organizacao-cadastrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Cadastrando...";
        btnSubmit.disabled = true;

        var formData = new FormData();
        formData.append("nome", nomeValor);
        formData.append("cnpj", cnpjValor);
        formData.append("senha", senhaValor);

        fetch("../../php/organizacao_adicionar.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta("Organizacao cadastrada com sucesso.", "success");
                formCadastro.reset();
            } else {
                mostrarAlerta(data.mensagem || "Nao foi possivel cadastrar a organizacao.", "danger");
            }
        })
        .catch(function (error) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;
            mostrarAlerta("Erro de conexao.", "danger");
            console.error(error);
        });
    });
});
