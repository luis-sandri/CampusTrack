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

    formCadastro.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var btnSubmit = document.getElementById("btn-organizacao-cadastrar");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Cadastrando...";
        btnSubmit.disabled = true;

        fetch("../../php/organizacao_adicionar.php", {
            method: "POST",
            body: new FormData(formCadastro)
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta(data.mensagem, "success");
                formCadastro.reset();
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
