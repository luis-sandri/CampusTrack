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

    formLogin.addEventListener("submit", async function (e) {
        e.preventDefault();
        esconderAlerta();

        var btnSubmit = document.getElementById("btn-entrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Entrando...";
        btnSubmit.disabled = true;

        const resposta = await CampusTrack.form.enviar(formLogin, "../../php/autenticacao/gerente_login.php");
        btnSubmit.textContent = originalText;
        btnSubmit.disabled = false;

        if (resposta.status === "ok") {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "success");
            setTimeout(function () {
                window.location.href = "gerenciar_local.html";
            }, 1000);
        } else {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "danger");
        }
    });
});
