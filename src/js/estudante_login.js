document.addEventListener("DOMContentLoaded", function () {
    var formEmail = document.getElementById("form-email");
    var formCodigo = document.getElementById("form-codigo");
    var alertaMsg = document.getElementById("alerta-msg");
    var inputEmail = document.getElementById("email");
    var inputIdInstituicao = document.getElementById("id_instituicao");
    var inputEmailVerificacao = document.getElementById("email_verificacao");
    var btnEnviarEmail = document.getElementById("btn-enviar-email");
    var btnVerificarCodigo = document.getElementById("btn-verificar-codigo");
    var btnVoltar = document.getElementById("btn-voltar");
    var instrucaoTexto = document.getElementById("instrucao-texto");
    var listaLocais = document.getElementById("lista-locais");
    var mapaLocais = document.getElementById("mapa-locais");
    var mapaVazio = document.getElementById("mapa-vazio");
    var linkVoltarMapa = document.getElementById("link-voltar-mapa");
    var linkCadastroAluno = document.getElementById("link-cadastro-aluno");
    var linkLoginAluno = document.getElementById("link-login-aluno");
    var btnMenuEstudante = document.getElementById("btn-menu-estudante");
    var itemSeparadorLogoutAluno = document.getElementById("item-separador-logout-aluno");
    var itemLogoutAluno = document.getElementById("item-logout-aluno");
    var btnLogoutAluno = document.getElementById("btn-logout-aluno");
    var inputModo = document.getElementById("modo");
    var tituloEstudante = document.getElementById("titulo-estudante");

    var urlParams = new URLSearchParams(window.location.search);
    var idInstituicao = urlParams.get("id_instituicao") || urlParams.get("id");
    var modoAluno = urlParams.get("modo") === "login" ? "login" : "cadastro";
    var textoInstrucaoInicial = modoAluno === "login"
        ? "Informe seu e-mail institucional e senha para receber o codigo de acesso."
        : "Preencha seus dados e valide o codigo enviado ao e-mail institucional.";

    configurarModoAluno();

    if (idInstituicao && /^\d+$/.test(idInstituicao)) {
        if (inputIdInstituicao) {
            inputIdInstituicao.value = idInstituicao;
        }

        var idInstituicaoTexto = document.getElementById("id-instituicao");
        if (idInstituicaoTexto) {
            idInstituicaoTexto.textContent = idInstituicao;
        }

        atualizarLinksAluno(idInstituicao);

        if (linkVoltarMapa) {
            linkVoltarMapa.href = "../visitante/instituicao.html?id=" + encodeURIComponent(idInstituicao);
        }

        if (mapaLocais) {
            carregarLocais(idInstituicao);
            verificarSessaoAluno();
        }
    } else if (inputIdInstituicao) {
        mostrarAlerta("Instituicao nao informada ou invalida.", "danger");
    }

    function configurarModoAluno() {
        if (inputModo) {
            inputModo.value = modoAluno;
        }

        if (tituloEstudante) {
            tituloEstudante.textContent = modoAluno === "login" ? "Entrada de Estudante" : "Cadastro de Estudante";
        }

        if (instrucaoTexto) {
            instrucaoTexto.textContent = textoInstrucaoInicial;
        }

        if (btnVerificarCodigo) {
            btnVerificarCodigo.textContent = modoAluno === "login" ? "Entrar" : "Validar";
        }

        if (inputModo || tituloEstudante) {
            document.title = modoAluno === "login" ? "CampusTrack - Entrada de Estudante" : "CampusTrack - Cadastro de Estudante";
        }

        alternarCampoCadastro("nome", modoAluno !== "login");
        alternarCampoCadastro("senha", true);
        alternarCampoCadastro("curso", modoAluno !== "login");

        var inputSenha = document.getElementById("senha");
        if (inputSenha) {
            inputSenha.title = modoAluno === "login"
                ? "Informe sua senha cadastrada."
                : "Minimo de 8 caracteres, com letra maiuscula, numero e simbolo.";
        }
    }

    function alternarCampoCadastro(idCampo, ativo) {
        var campo = document.getElementById(idCampo);
        if (!campo) {
            return;
        }

        var grupo = campo.closest(".form-group");

        campo.disabled = !ativo;
        campo.required = ativo;

        if (grupo) {
            if (ativo) {
                grupo.classList.remove("d-none");
            } else {
                grupo.classList.add("d-none");
            }
        }
    }

    function atualizarLinksAluno(id) {
        var queryCadastro = "?id_instituicao=" + encodeURIComponent(id) + "&modo=cadastro";
        var queryLogin = "?id_instituicao=" + encodeURIComponent(id) + "&modo=login";

        if (linkCadastroAluno) {
            linkCadastroAluno.href = "../estudante/login.html" + queryCadastro;
        }

        if (linkLoginAluno) {
            linkLoginAluno.href = "../estudante/login.html" + queryLogin;
        }
    }

    function mostrarAlerta(mensagem, tipo) {
        if (!alertaMsg) {
            return;
        }

        alertaMsg.textContent = mensagem;
        alertaMsg.className = "alert alert-" + tipo;
    }

    function esconderAlerta() {
        if (!alertaMsg) {
            return;
        }

        alertaMsg.className = "alert d-none";
    }

    function validarDadosCadastro() {
        var emailValor = inputEmail ? inputEmail.value.trim() : "";
        var idInstituicaoValor = inputIdInstituicao ? inputIdInstituicao.value.trim() : "";
        var inputNome = document.getElementById("nome");
        var inputSenha = document.getElementById("senha");
        var inputCurso = document.getElementById("curso");
        var nomeValor = inputNome ? inputNome.value.trim() : "";
        var senhaValor = inputSenha ? inputSenha.value.trim() : "";
        var cursoValor = inputCurso ? inputCurso.value.trim() : "";

        if (idInstituicaoValor === "" || !/^\d+$/.test(idInstituicaoValor)) {
            mostrarAlerta("Instituicao nao informada ou invalida.", "danger");
            return false;
        }

        if (!emailValor.endsWith("@pucpr.edu.br")) {
            mostrarAlerta("O e-mail deve ser do dominio @pucpr.edu.br", "danger");
            return false;
        }

        if (modoAluno === "login" && senhaValor === "") {
            mostrarAlerta("E-mail e senha sao obrigatorios.", "danger");
            return false;
        }

        if (modoAluno !== "login" && (nomeValor === "" || senhaValor === "" || cursoValor === "")) {
            mostrarAlerta("Nome, e-mail, senha e curso sao obrigatorios.", "danger");
            return false;
        }

        if (modoAluno !== "login" && inputSenha && !validarSenha(senhaValor)) {
            mostrarAlerta("A senha deve ter pelo menos 8 caracteres, 1 letra maiuscula, 1 numero e 1 simbolo.", "danger");
            return false;
        }

        if (inputEmailVerificacao) {
            inputEmailVerificacao.value = emailValor;
        }

        return true;
    }

    function validarSenha(senha) {
        return senha.length >= 8 && /[A-Z]/.test(senha) && /\d/.test(senha) && /[^a-zA-Z0-9]/.test(senha);
    }

    function enviarCodigo() {
        esconderAlerta();

        if (!validarDadosCadastro()) {
            return;
        }

        var originalText = btnEnviarEmail.textContent;
        btnEnviarEmail.textContent = "Enviando...";
        btnEnviarEmail.disabled = true;

        var formData = new FormData();
        formData.append("email", inputEmail.value.trim());
        formData.append("id_instituicao", inputIdInstituicao.value.trim());
        formData.append("modo", modoAluno);
        var inputSenha = document.getElementById("senha");
        if (inputSenha) {
            formData.append("senha", inputSenha.value.trim());
        }

        fetch("../../php/codigo_enviar.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            btnEnviarEmail.textContent = originalText;
            btnEnviarEmail.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta("Codigo enviado com sucesso!", "success");
                if (instrucaoTexto) {
                    instrucaoTexto.textContent = "Insira o codigo de 6 digitos enviado para o seu e-mail.";
                }
            } else {
                mostrarAlerta(data.mensagem || "Erro ao enviar codigo.", "danger");
            }
        })
        .catch(function (error) {
            btnEnviarEmail.textContent = originalText;
            btnEnviarEmail.disabled = false;
            mostrarAlerta("Erro de conexao.", "danger");
            console.error(error);
        });
    }

    function validarCodigo(formulario) {
        esconderAlerta();

        if (!validarDadosCadastro()) {
            return;
        }

        var originalText = btnVerificarCodigo.textContent;
        btnVerificarCodigo.textContent = "Validando...";
        btnVerificarCodigo.disabled = true;

        var formData = new FormData(formulario);
        formData.set("email_verificacao", inputEmail.value.trim());
        formData.set("id_instituicao", inputIdInstituicao.value.trim());
        formData.set("modo", modoAluno);

        fetch("../../php/codigo_verificar.php", {
            method: "POST",
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            btnVerificarCodigo.textContent = originalText;
            btnVerificarCodigo.disabled = false;

            if (data.status === "ok") {
                mostrarAlerta(data.mensagem || "Acesso validado com sucesso.", "success");
                setTimeout(function () {
                    if (linkVoltarMapa && linkVoltarMapa.getAttribute("href")) {
                        window.location.href = linkVoltarMapa.getAttribute("href");
                    } else if (idInstituicao && /^\d+$/.test(idInstituicao)) {
                        window.location.href = "../visitante/instituicao.html?id=" + encodeURIComponent(idInstituicao);
                    } else {
                        window.location.href = "../index.html";
                    }
                }, 1000);
            } else {
                mostrarAlerta(data.mensagem || "Codigo invalido.", "danger");
            }
        })
        .catch(function (error) {
            btnVerificarCodigo.textContent = originalText;
            btnVerificarCodigo.disabled = false;
            mostrarAlerta("Erro de conexao.", "danger");
            console.error(error);
        });
    }

    function carregarLocais(id) {
        fetch("../../php/local_get.php?id_instituicao=" + encodeURIComponent(id))
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            if (resposta.status !== "ok" || !Array.isArray(resposta.data)) {
                if (listaLocais) {
                    listaLocais.innerHTML = '<div class="col-12 text-muted">Nao foi possivel carregar os locais.</div>';
                }
                if (mapaVazio) {
                    mapaVazio.textContent = "Nao foi possivel carregar o mapa.";
                }
                return;
            }

            var registros = resposta.data;

            if (registros.length === 0) {
                if (listaLocais) {
                    listaLocais.innerHTML = '<div class="col-12 text-muted">Nenhum local cadastrado para esta instituicao.</div>';
                }
                if (mapaVazio) {
                    mapaVazio.textContent = "Mapa sem pontos cadastrados para esta instituicao.";
                }
                return;
            }

            if (mapaVazio) {
                mapaVazio.remove();
            }

            var html = "";
            for (var i = 0; i < registros.length; i++) {
                var local = registros[i];
                html += '<div class="col-md-6">';
                html += '<div class="border rounded-3 p-3 h-100 bg-light">';
                html += '<h3 class="h6 fw-bold mb-1">' + escapeHtml(local.nome) + '</h3>';
                html += '<p class="small text-muted mb-2">' + escapeHtml(local.tipo_escola) + ' - ' + escapeHtml(local.tipo) + '</p>';
                html += '<p class="small mb-0">Capacidade: ' + escapeHtml(local.capacidade) + '</p>';
                html += '<p class="small mb-0">Longitude: ' + escapeHtml(local.longitude) + '</p>';
                html += '<p class="small mb-0">Latitude: ' + escapeHtml(local.latitude) + '</p>';
                html += '</div>';
                html += '</div>';

                adicionarPinoNoMapa(local, i, registros.length);
            }

            if (listaLocais) {
                listaLocais.innerHTML = html;
            }
        })
        .catch(function (error) {
            if (listaLocais) {
                listaLocais.innerHTML = '<div class="col-12 text-muted">Erro de conexao ao carregar os locais.</div>';
            }
            if (mapaVazio) {
                mapaVazio.textContent = "Erro de conexao ao carregar o mapa.";
            }
            console.error(error);
        });
    }

    function verificarSessaoAluno() {
        fetch("../../php/sessao_status.php?perfil=aluno")
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            if (resposta.status !== "ok" || !Array.isArray(resposta.data) || resposta.data.length === 0) {
                document.body.setAttribute("data-aluno-logado", "false");
                exibirLogoutAluno(false);
                return;
            }

            document.body.setAttribute("data-aluno-logado", "true");
            document.body.setAttribute("data-aluno-nome", resposta.data[0].nome || "");

            if (btnMenuEstudante) {
                btnMenuEstudante.textContent = resposta.data[0].nome || "Estudante";
            }

            desabilitarLinkAluno(linkCadastroAluno);
            desabilitarLinkAluno(linkLoginAluno);
            exibirLogoutAluno(true);
        })
        .catch(function () {
            document.body.setAttribute("data-aluno-logado", "false");
            exibirLogoutAluno(false);
        });
    }

    function exibirLogoutAluno(ativo) {
        alternarItemLogout(itemSeparadorLogoutAluno, ativo);
        alternarItemLogout(itemLogoutAluno, ativo);
    }

    function alternarItemLogout(item, ativo) {
        if (!item) {
            return;
        }

        if (ativo) {
            item.classList.remove("d-none");
        } else {
            item.classList.add("d-none");
        }
    }

    function desabilitarLinkAluno(link) {
        if (!link) {
            return;
        }

        link.classList.add("disabled");
        link.removeAttribute("href");
        link.setAttribute("aria-disabled", "true");
        link.setAttribute("tabindex", "-1");
    }

    function adicionarPinoNoMapa(local, indice, total) {
        if (!mapaLocais) {
            return;
        }

        var pino = document.createElement("span");
        pino.className = "ct-map-pin";
        pino.title = local.nome;
        pino.setAttribute("aria-label", local.nome);

        var coluna = indice % 4;
        var linha = Math.floor(indice / 4);
        var left = 12 + (coluna * 22);
        var top = 18 + ((linha % Math.max(1, Math.ceil(total / 4))) * 22);

        pino.style.left = Math.min(left, 88) + "%";
        pino.style.top = Math.min(top, 86) + "%";
        mapaLocais.appendChild(pino);
    }

    function escapeHtml(valor) {
        return String(valor === null || valor === undefined ? "" : valor)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function sairAluno() {
        if (btnLogoutAluno) {
            btnLogoutAluno.disabled = true;
        }

        fetch("../../php/logout.php")
        .then(function (response) {
            return response.json();
        })
        .then(function () {
            if (idInstituicao && /^\d+$/.test(idInstituicao)) {
                window.location.href = "../visitante/instituicao.html?id=" + encodeURIComponent(idInstituicao);
            } else {
                window.location.href = "../index.html";
            }
        })
        .catch(function (error) {
            if (btnLogoutAluno) {
                btnLogoutAluno.disabled = false;
            }
            console.error(error);
        });
    }

    if (btnEnviarEmail) {
        btnEnviarEmail.addEventListener("click", enviarCodigo);
    }

    if (formEmail) {
        formEmail.addEventListener("submit", function (e) {
            e.preventDefault();
            validarCodigo(formEmail);
        });
    }

    if (formCodigo) {
        formCodigo.addEventListener("submit", function (e) {
            e.preventDefault();
            validarCodigo(formCodigo);
        });
    }

    if (btnVoltar && formCodigo && formEmail) {
        btnVoltar.addEventListener("click", function() {
            formCodigo.classList.add("d-none");
            formEmail.classList.remove("d-none");
            document.getElementById("codigo").value = "";
            if (instrucaoTexto) {
                instrucaoTexto.textContent = textoInstrucaoInicial;
            }
            esconderAlerta();
        });
    }

    if (btnLogoutAluno) {
        btnLogoutAluno.addEventListener("click", sairAluno);
    }
});
