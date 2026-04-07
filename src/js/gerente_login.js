document.addEventListener("DOMContentLoaded", function () {
    var formLogin = document.getElementById("form-login");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputEmail = document.getElementById("email");
    var inputSenha = document.getElementById("senha");

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    formLogin.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var emailValor = inputEmail.value.trim();
        var senhaValor = inputSenha.value.trim();

        if (emailValor === "" || senhaValor === "") {
            mostrarAlerta("Preencha todos os campos.", "danger");
            return;
        }

        var btnSubmit = document.getElementById("btn-entrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Entrando...";
        btnSubmit.disabled = true;

        var formData = new FormData(formLogin);

        fetch("../../php/gerente_login.php", {
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
                    window.location.href = "../gerente/gerenciar_local.html";
                }, 1000);
            } else {
                mostrarAlerta(data.mensagem || "E-mail ou senha inválidos.", "danger");
            }
        })
        .catch(function (error) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;
            mostrarAlerta("Erro de conexão.", "danger");
            console.error(error);
        });
    });
});