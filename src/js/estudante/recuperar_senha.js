document.addEventListener("DOMContentLoaded", function () {
    var formEmail = document.getElementById("form-email");
    var formNovaSenha = document.getElementById("form-nova-senha");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputEmail = document.getElementById("email");
    var inputCodigo = document.getElementById("codigo");
    var inputSenhaNova = document.getElementById("senha_nova");
    var btnEnviarEmail = document.getElementById("btn-enviar-email");
    var btnRedefinir = document.getElementById("btn-redefinir");
    var btnVoltar = document.getElementById("btn-voltar");
    var instrucaoTexto = document.getElementById("instrucao-texto");
    var instrucaoInicial = instrucaoTexto.textContent;

    function mostrarAlerta(mensagem, tipo) {
        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert w-100 alert-" + tipo;
    }

    function esconderAlerta() {
        alertaMsg.className = "alert d-none w-100";
    }

    function alternarBotao(botao, texto, desabilitado) {
        botao.textContent = texto;
        botao.disabled = desabilitado;
    }

    formEmail.addEventListener("submit", async function (e) {
        e.preventDefault();
        esconderAlerta();

        var textoOriginal = btnEnviarEmail.textContent;
        alternarBotao(btnEnviarEmail, "Enviando...", true);

        const resposta = await CampusTrack.form.enviar(formEmail, "../../php/recuperacao_senha/enviar.php");
        alternarBotao(btnEnviarEmail, textoOriginal, false);

        if (resposta.status === "ok") {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "success");
            formEmail.classList.add("d-none");
            formNovaSenha.classList.remove("d-none");
            instrucaoTexto.textContent = "Insira o codigo de 6 digitos recebido no e-mail e defina a nova senha.";
        } else {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "danger");
        }
    });

    formNovaSenha.addEventListener("submit", async function (e) {
        e.preventDefault();
        esconderAlerta();

        var textoOriginal = btnRedefinir.textContent;
        alternarBotao(btnRedefinir, "Redefinindo...", true);

        var formData = new FormData(formNovaSenha);
        formData.append("email", inputEmail.value.trim());

        const resposta = await CampusTrack.api.json("../../php/recuperacao_senha/nova_usuario.php", {
            method: "POST",
            body: formData,
        });
        alternarBotao(btnRedefinir, textoOriginal, false);

        if (resposta.status === "ok") {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta) + " Redirecionando...", "success");
            setTimeout(function () {
                window.location.href = "login.html";
            }, 2000);
        } else {
            mostrarAlerta(CampusTrack.resposta.mensagem(resposta), "danger");
        }
    });

    btnVoltar.addEventListener("click", function () {
        formNovaSenha.classList.add("d-none");
        formEmail.classList.remove("d-none");
        inputCodigo.value = "";
        inputSenhaNova.value = "";
        instrucaoTexto.textContent = instrucaoInicial;
        esconderAlerta();
    });
});
