document.addEventListener("DOMContentLoaded", function () {
    var formLogin = document.getElementById("form-login");
    var alertaMsg = document.getElementById("alerta-msg");

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

        var btnSubmit = document.getElementById("btn-entrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Entrando...";
        btnSubmit.disabled = true;

        fetch("../../php/gerente_login.php", {
            method: "POST",
            body: new FormData(formLogin)
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta(data.mensagem, "success");
                setTimeout(function () {
                    window.location.href = "gerenciar_local.html";
                }, 1000);
            } else {
                mostrarAlerta(data.mensagem, "danger");
            }
        })
        .catch(function (error) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;
            mostrarAlerta("Erro de conexao: " + error.message, "danger");
            console.error(error);
        });
    });
});
