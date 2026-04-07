document.addEventListener("DOMContentLoaded", function () {
    var formEmail = document.getElementById("form-email");
    var formCodigo = document.getElementById("form-codigo");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputEmail = document.getElementById("email");
    var inputIdInstituicao = document.getElementById("id_instituicao");
    var inputEmailVerificacao = document.getElementById("email_verificacao");
    var btnVoltar = document.getElementById("btn-voltar");
    var instrucaoTexto = document.getElementById("instrucao-texto");

    // Pegar o ID da instituição da URL
    var urlParams = new URLSearchParams(window.location.search);
    var idInstituicao = urlParams.get('id_instituicao');
    if (idInstituicao) {
        inputIdInstituicao.value = idInstituicao;
    }

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    formEmail.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var emailValor = inputEmail.value.trim();
        if (!emailValor.endsWith("@pucpr.edu.br")) {
            mostrarAlerta("O e-mail deve ser do domínio @pucpr.edu.br", "danger");
            return;
        }

        var btnSubmit = document.getElementById("btn-enviar-email");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Enviando...";
        btnSubmit.disabled = true;

        var formData = new FormData(formEmail);

        fetch("../../php/codigo_enviar.php", {
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
                inputEmailVerificacao.value = emailValor;
                formEmail.classList.add("d-none");
                formCodigo.classList.remove("d-none");
                instrucaoTexto.textContent = "Insira o código de 6 dígitos enviado para o seu e-mail.";
                mostrarAlerta("Código enviado com sucesso!", "success");
            } else {
                mostrarAlerta(data.mensagem || "Erro ao enviar código.", "danger");
            }
        })
        .catch(function (error) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;
            mostrarAlerta("Erro de conexão.", "danger");
            console.error(error);
        });
    });

    formCodigo.addEventListener("submit", function (e) {
        e.preventDefault();
        esconderAlerta();

        var btnSubmit = document.getElementById("btn-verificar-codigo");
        var originalText = btnSubmit.textContent;
        btnSubmit.textContent = "Verificando...";
        btnSubmit.disabled = true;

        var formData = new FormData(formCodigo);

        fetch("../../php/codigo_verificar.php", {
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
                setTimeout(function() {
                    window.location.href = "dashboard.html?email=" + encodeURIComponent(inputEmailVerificacao.value);
                }, 1000);
            } else {
                mostrarAlerta(data.mensagem || "Código inválido.", "danger");
            }
        })
        .catch(function (error) {
            btnSubmit.textContent = originalText;
            btnSubmit.disabled = false;
            mostrarAlerta("Erro de conexão.", "danger");
            console.error(error);
        });
    });

    btnVoltar.addEventListener("click", function() {
        formCodigo.classList.add("d-none");
        formEmail.classList.remove("d-none");
        document.getElementById("codigo").value = "";
        instrucaoTexto.textContent = "Informe seu e-mail institucional para acessar.";
        esconderAlerta();
    });
});
