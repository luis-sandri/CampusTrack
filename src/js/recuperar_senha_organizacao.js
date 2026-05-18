// Recuperacao de senha para Organizacao (CNPJ + e-mail de contato)
document.addEventListener("DOMContentLoaded", function () {
    var formEmail      = document.getElementById("form-email");
    var formNovaSenha  = document.getElementById("form-nova-senha");
    var alertaMsg      = document.getElementById("alerta-msg");
    var inputCnpj      = document.getElementById("cnpj");
    var inputEmail     = document.getElementById("email");
    var inputCodigo    = document.getElementById("codigo");
    var inputSenhaNova = document.getElementById("senha_nova");
    var btnEnviarEmail = document.getElementById("btn-enviar-email");
    var btnRedefinir   = document.getElementById("btn-redefinir");
    var btnVoltar      = document.getElementById("btn-voltar");
    var instrucaoTexto = document.getElementById("instrucao-texto");

    var instrucaoInicial = instrucaoTexto ? instrucaoTexto.textContent : "";

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    function validarSenha(senha) {
        return senha.length >= 8 && /[A-Z]/.test(senha) && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
    }

    formEmail.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var cnpjValor  = inputCnpj  ? inputCnpj.value.trim()  : "";
        var emailValor = inputEmail ? inputEmail.value.trim() : "";

        if (cnpjValor === "" || emailValor === "") {
            mostrarAlerta("CNPJ e e-mail de contato são obrigatórios.", "danger");
            return;
        }

        var originalText = btnEnviarEmail.textContent;
        btnEnviarEmail.textContent = "Enviando...";
        btnEnviarEmail.disabled = true;

        var formData = new FormData(formEmail);

        fetch("../../php/recuperar_senha_enviar.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            btnEnviarEmail.textContent = originalText;
            btnEnviarEmail.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta(data.mensagem, "success");
                formEmail.classList.add("d-none");
                formNovaSenha.classList.remove("d-none");
                if (instrucaoTexto) {
                    instrucaoTexto.textContent = "Insira o código de 6 dígitos recebido no e-mail e defina a nova senha.";
                }
            } else {
                mostrarAlerta(data.mensagem || "Erro ao solicitar recuperação.", "danger");
            }
        })
        .catch(function (error) {
            btnEnviarEmail.textContent = originalText;
            btnEnviarEmail.disabled = false;
            mostrarAlerta("Erro de conexão.", "danger");
            console.error(error);
        });
    });

    formNovaSenha.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var cnpjValor   = inputCnpj       ? inputCnpj.value.trim()       : "";
        var codigoValor = inputCodigo      ? inputCodigo.value.trim()     : "";
        var senhaValor  = inputSenhaNova   ? inputSenhaNova.value.trim()  : "";

        if (!validarSenha(senhaValor)) {
            mostrarAlerta("A senha deve ter pelo menos 8 caracteres, 1 letra maiúscula, 1 número e 1 símbolo.", "danger");
            return;
        }

        var originalText = btnRedefinir.textContent;
        btnRedefinir.textContent = "Redefinindo...";
        btnRedefinir.disabled = true;

        var formData = new FormData();
        formData.append("cnpj",       cnpjValor);
        formData.append("codigo",     codigoValor);
        formData.append("senha_nova", senhaValor);

        fetch("../../php/recuperar_senha_nova_organizacao.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            btnRedefinir.textContent = originalText;
            btnRedefinir.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta("Senha redefinida com sucesso! Redirecionando...", "success");
                setTimeout(function () {
                    window.location.href = "login.html";
                }, 2000);
            } else {
                mostrarAlerta(data.mensagem || "Erro ao redefinir a senha.", "danger");
            }
        })
        .catch(function (error) {
            btnRedefinir.textContent = originalText;
            btnRedefinir.disabled = false;
            mostrarAlerta("Erro de conexão.", "danger");
            console.error(error);
        });
    });

    btnVoltar.addEventListener("click", function () {
        formNovaSenha.classList.add("d-none");
        formEmail.classList.remove("d-none");
        inputCodigo.value     = "";
        inputSenhaNova.value  = "";
        if (instrucaoTexto) {
            instrucaoTexto.textContent = instrucaoInicial;
        }
        esconderAlerta();
    });
});
