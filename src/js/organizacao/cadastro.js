document.addEventListener("DOMContentLoaded", function () {
    var formCadastro = document.getElementById("form-organizacao-cadastro");
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

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    formCadastro.addEventListener("submit", async function (e) {
        e.preventDefault();
        esconderAlerta();

        var btnSubmit = document.getElementById("btn-organizacao-cadastrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Cadastrando...";
        btnSubmit.disabled = true;

        const resposta = await CampusTrack.form.enviar(formCadastro, "../../php/organizacoes/adicionar.php");
        btnSubmit.textContent = originalText;
        btnSubmit.disabled = false;

        if (resposta.status === "ok") {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "success");
            formCadastro.reset();
        } else {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "danger");
        }
    });
});
