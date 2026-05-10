document.addEventListener("DOMContentLoaded", function () {
    var formOrganizacaoLogin = document.getElementById("form-organizacao-login");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputCnpj = document.getElementById("organizacao-cnpj");
    var inputSenha = document.getElementById("organizacao-senha");

    function mostrarAlerta(mensagem, tipoAlerta) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipoAlerta;
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

    formOrganizacaoLogin.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var cnpjValor = limparCnpj(inputCnpj.value.trim());
        var senhaValor = inputSenha.value.trim();

        if (cnpjValor === "" || senhaValor === "") {
            mostrarAlerta("Informe CNPJ e senha.", "danger");
            return;
        }

        if (!validarCnpj(cnpjValor)) {
            mostrarAlerta("CNPJ invalido.", "danger");
            return;
        }

        var btnSubmit = document.getElementById("btn-organizacao-entrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Entrando...";
        btnSubmit.disabled = true;

        var formData = new FormData();
        formData.append("cnpj", cnpjValor);
        formData.append("senha", senhaValor);

        fetch("../../php/organizacao_login.php", {
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
                mostrarAlerta("Acesso validado! Redirecionando...", "success");
                setTimeout(function () {
                    window.location.href = "gerenciar_organizador.html";
                }, 1000);
            } else {
                mostrarAlerta(data.mensagem || "Organizacao nao encontrada.", "danger");
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
