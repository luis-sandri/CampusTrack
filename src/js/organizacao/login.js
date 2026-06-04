document.addEventListener("DOMContentLoaded", function () {
    var formOrganizacaoLogin = document.getElementById("form-organizacao-login");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputCnpj = document.getElementById("organizacao-cnpj");

    function mascaraCnpj(valor) {
        valor = valor.replace(/\D/g, "");
        valor = valor.substring(0, 14);
        valor = valor.replace(/^(\d{2})(\d)/, "$1.$2");
        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        valor = valor.replace(/\.(\d{3})(\d)/, ".$1/$2");
        valor = valor.replace(/(\d{4})(\d)/, "$1-$2");
        return valor;
    }

    inputCnpj.addEventListener("input", function () {
        this.value = mascaraCnpj(this.value);
    });

    function mostrarAlerta(mensagem, tipoAlerta) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipoAlerta;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    formOrganizacaoLogin.addEventListener("submit", async function (e) {
        e.preventDefault();
        esconderAlerta();

        var btnSubmit = document.getElementById("btn-organizacao-entrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Entrando...";
        btnSubmit.disabled = true;

        const resposta = await CampusTrack.form.enviar(formOrganizacaoLogin, "../../php/autenticacao/organizacao_login.php");
        btnSubmit.textContent = originalText;
        btnSubmit.disabled = false;

        if (resposta.status === "ok") {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "success");
            setTimeout(function () {
                window.location.href = "organizadores.html";
            }, 1000);
        } else {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "danger");
        }
    });
});
