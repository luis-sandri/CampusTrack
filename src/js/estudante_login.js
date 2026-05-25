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
    var painelMapa = document.querySelector(".ct-map-panel");
    var mapaVazio = document.getElementById("mapa-vazio");
    var buscaLocal = document.getElementById("busca-local");
    var btnLimparBusca = document.getElementById("btn-limpar-busca");
    var resultadoLocais = document.getElementById("resultado-locais");
    var btnLocalizarEstudante = document.getElementById("btn-localizar-estudante");
    var btnLimparRota = document.getElementById("btn-limpar-rota");
    var mapaStatus = document.getElementById("mapa-status");
    var linkVoltarMapa = document.getElementById("link-voltar-mapa");
    var linkCadastroAluno = document.getElementById("link-cadastro-aluno");
    var linkLoginAluno = document.getElementById("link-login-aluno");
    var linkFavoritosAluno = document.getElementById("link-favoritos-aluno");
    var itemFavoritosAluno = document.getElementById("item-favoritos-aluno");
    var btnMenuEstudante = document.getElementById("btn-menu-estudante");
    var itemSeparadorLogoutAluno = document.getElementById("item-separador-logout-aluno");
    var itemLogoutAluno = document.getElementById("item-logout-aluno");
    var btnLogoutAluno = document.getElementById("btn-logout-aluno");
    var inputModo = document.getElementById("modo");
    var idsFavoritados = new Set();
    var tituloEstudante = document.getElementById("titulo-estudante");
    var linksNavegacaoAluno = document.querySelectorAll(".ct-desktop-nav a, .ct-mobile-tabbar a");

    var urlParams = new URLSearchParams(window.location.search);
    var idInstituicao = urlParams.get("id_instituicao") || urlParams.get("id");
    var coordenadasPadrao = [-25.45275, -49.25083];
    var mapaLeaflet = null;
    var camadaLocais = null;
    var camadaRota = null;
    var marcadoresLocais = {};
    var locaisMapa = [];
    var grafoMapa = { nos: [], arestas: [] };
    var grafoCarregado = false;
    var grafoDisponivel = false;
    var grafoPromise = null;
    var localSelecionado = null;
    var favoritosAluno = {};
    var posicaoUsuario = null;
    var marcadorUsuario = null;
    var raioUsuario = null;
    var watchLocalizacaoId = null;
    var resizeMapaRegistrado = false;
    var localizacaoInicialSolicitada = false;
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
        atualizarLinksNavegacaoAluno(idInstituicao);

        if (linkVoltarMapa) {
            linkVoltarMapa.href = "../visitante/instituicao.html?id=" + encodeURIComponent(idInstituicao);
        }

        if (mapaLocais) {
            verificarSessaoAluno();
            carregarFavoritos();
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

        if (linkFavoritosAluno) {
            linkFavoritosAluno.href = "../estudante/favoritos.html";
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
                var locaisFixos = obterLocaisFixosInstituicao(id);

                if (locaisFixos.length > 0) {
                    carregarLocaisNoMapa(locaisFixos, "Nao foi possivel carregar o banco. Exibindo locais fixos da PUCPR.", "warning");
                } else {
                    if (listaLocais) {
                        listaLocais.innerHTML = '<div class="col-12 text-muted">Nao foi possivel carregar os locais.</div>';
                    }
                    if (mapaVazio) {
                        mapaVazio.textContent = "Nao foi possivel carregar o mapa.";
                    }
                    atualizarMapaStatus("Nao foi possivel carregar os locais da instituicao.", "danger");
                }
                return;
            }

            carregarLocaisNoMapa(resposta.data, "Pesquise um bloco ou selecione um marcador no mapa.");
        })
        .catch(function (error) {
            if (listaLocais) {
                listaLocais.innerHTML = '<div class="col-12 text-muted">Erro de conexao ao carregar os locais.</div>';
            }
            if (mapaVazio) {
                mapaVazio.textContent = "Erro de conexao ao carregar o mapa.";
            }
            atualizarMapaStatus("Erro de conexao ao carregar o mapa.", "danger");

            console.error(error);
        });
    }

    function carregarGrafo(id) {
        grafoMapa = { nos: [], arestas: [] };
        grafoCarregado = false;
        grafoDisponivel = false;
        grafoPromise = null;

        grafoPromise = fetch("../../php/mapa_grafo_get.php?id_instituicao=" + encodeURIComponent(id))
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            if (resposta.status !== "ok" || !Array.isArray(resposta.data) || resposta.data.length === 0) {
                return;
            }

            grafoMapa = prepararGrafoMapa(resposta.data[0]);
            grafoDisponivel = grafoMapa.nos.length > 0 && grafoMapa.arestas.length > 0;
        })
        .catch(function (error) {
            console.error(error);
        })
        .finally(function () {
            grafoCarregado = true;

            if (localSelecionado && posicaoUsuario) {
                desenharRotaAproximada(localSelecionado);
            }
        });

            var alunoLogado = document.body.getAttribute("data-aluno-logado") === "true";

            var html = "";
            for (var i = 0; i < registros.length; i++) {
                var local = registros[i];
                var favoritado = idsFavoritados.has(String(local.id_local));
                var btnFavorito = "";

                if (alunoLogado) {
                    btnFavorito =
                        '<button ' +
                            'class="btn btn-sm ct-btn-favorito ' + (favoritado ? 'btn-warning' : 'btn-outline-secondary') + ' ms-auto" ' +
                            'data-id-local="' + escapeHtml(local.id_local) + '" ' +
                            'title="' + (favoritado ? 'Remover dos favoritos' : 'Adicionar aos favoritos') + '" ' +
                            'aria-label="' + (favoritado ? 'Remover ' : 'Favoritar ') + escapeHtml(local.nome) + '" ' +
                            'aria-pressed="' + (favoritado ? 'true' : 'false') + '">' +
                            (favoritado ? '★' : '☆') +
                        '</button>';
                }

                html += '<div class="col-md-6">';
                html += '<div class="border rounded-3 p-3 h-100 bg-light">';
                html += '<div class="d-flex align-items-start gap-2 mb-1">';
                html += '<h3 class="h6 fw-bold mb-0 flex-grow-1">' + escapeHtml(local.nome) + '</h3>';
                html += btnFavorito;
                html += '</div>';
                html += '<p class="small text-muted mb-2">' + escapeHtml(local.tipo_escola) + ' - ' + escapeHtml(local.tipo) + '</p>';
                html += '<p class="small mb-0">Capacidade: ' + escapeHtml(local.capacidade) + '</p>';
                html += '<p class="small mb-0">Longitude: ' + escapeHtml(local.longitude) + '</p>';
                html += '<p class="small mb-0">Latitude: ' + escapeHtml(local.latitude) + '</p>';
                html += '</div>';
                html += '</div>';

        if (registros.length > 0 && registros[0].nome_instituicao) {
            var idInstituicaoTexto = document.getElementById("id-instituicao");
            if (idInstituicaoTexto) {
                idInstituicaoTexto.textContent = registros[0].nome_instituicao;
            }
        }

        if (locaisMapa.length === 0) {
            if (listaLocais) {
                listaLocais.innerHTML = html;

                // Vincula eventos de favorito após render
                var botoes = listaLocais.querySelectorAll(".ct-btn-favorito");
                for (var j = 0; j < botoes.length; j++) {
                    botoes[j].addEventListener("click", aoClicarFavorito);
                }
            }
            if (mapaVazio) {
                mapaVazio.textContent = "Nenhum local com coordenadas validas para esta instituicao.";
            }
            atualizarMapaStatus("Cadastre latitude e longitude nos locais para exibir o mapa.", "warning");
            return;
        }

        if (!inicializarMapa()) {
            atualizarResultadosBusca();
            return;
        }

        if (mapaVazio) {
            mapaVazio.classList.add("d-none");
        }

        desenharLocaisNoMapa();
        atualizarResultadosBusca();
        ajustarMapaAosLocais();
        atualizarMapaStatus(mensagem, tipoMensagem);
        solicitarLocalizacaoInicial();

        if (listaLocais) {
            listaLocais.innerHTML = montarListaLocais(locaisMapa);
        }
    }

    function obterLocaisFixosInstituicao(id) {
        if (String(id) !== "1") {
            return [];
        }

        return [
            {
                id_local: "pucpr-bloco-10",
                id_instituicao: 1,
                tipo_escola: "Politecnica",
                tipo: "Bloco",
                nome: "Bloco 10 - Cinza",
                capacidade: "",
                longitude: "-49.24988010957719",
                latitude: "-25.448778468099164",
                nome_instituicao: "PUCPR Curitiba",
                fixo: true,
                observacao: "Local fixo desta branch"
            },
            {
                id_local: "pucpr-bloco-5",
                id_instituicao: 1,
                tipo_escola: "Belas Artes",
                tipo: "Bloco",
                nome: "Bloco 5 - Vermelho",
                capacidade: "",
                longitude: "-49.25138814892537",
                latitude: "-25.449208632378372",
                nome_instituicao: "PUCPR Curitiba",
                fixo: true,
                observacao: "Local fixo desta branch"
            },
            {
                id_local: "pucpr-bloco-1",
                id_instituicao: 1,
                tipo_escola: "Educacao e Humanidades",
                tipo: "Bloco",
                nome: "Bloco 1",
                capacidade: "",
                longitude: "-49.2523",
                latitude: "-25.4521",
                nome_instituicao: "PUCPR Curitiba",
                fixo: true,
                observacao: "Local fixo desta branch"
            },
            {
                id_local: "pucpr-digital-arena",
                id_instituicao: 1,
                tipo_escola: "Digital Arena",
                tipo: "Auditorio",
                nome: "FTD Digital Arena",
                capacidade: "116",
                longitude: "-49.251924784316095",
                latitude: "-25.452812382769036",
                nome_instituicao: "PUCPR Curitiba",
                fixo: true,
                observacao: "Local fixo desta branch"
            }
        ];
    }

    function inicializarMapa() {
        if (!mapaLocais) {
            return false;
        }

        if (typeof L === "undefined") {
            if (mapaVazio) {
                mapaVazio.textContent = "Nao foi possivel carregar a biblioteca do mapa.";
            }
            atualizarMapaStatus("Verifique sua conexao para carregar o mapa interativo.", "danger");
            return false;
        }

        if (mapaLeaflet) {
            return true;
        }

        mapaLeaflet = L.map(mapaLocais, {
            zoomControl: false,
            minZoom: 15
        }).setView(coordenadasPadrao, 17);

        mapaLeaflet.attributionControl.setPrefix(false);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 20,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapaLeaflet);

        L.control.zoom({
            position: "bottomright"
        }).addTo(mapaLeaflet);

        camadaLocais = L.layerGroup().addTo(mapaLeaflet);
        camadaRota = L.layerGroup().addTo(mapaLeaflet);

        registrarResizeMapa();

        setTimeout(function () {
            mapaLeaflet.invalidateSize();
        }, 0);

        return true;
    }

    function registrarResizeMapa() {
        if (resizeMapaRegistrado) {
            return;
        }

        resizeMapaRegistrado = true;
        window.addEventListener("resize", function () {
            if (!mapaLeaflet) {
                return;
            }

            setTimeout(function () {
                mapaLeaflet.invalidateSize();
            }, 150);
        });
    }

    function carregarFavoritosAluno(idInstituicaoParam) {
        fetch("../../php/favorito_get.php")
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            idsFavoritados = new Set();
            if (resposta.status === "ok" && Array.isArray(resposta.data)) {
                for (var i = 0; i < resposta.data.length; i++) {
                    idsFavoritados.add(String(resposta.data[i].id_local));
                }
            }
            // Re-renderiza os locais já carregados com o estado correto de favorito
            carregarLocais(idInstituicaoParam);
        })
        .catch(function () {
            carregarLocais(idInstituicaoParam);
        });
    }

    function aoClicarFavorito(event) {
        var botao   = event.currentTarget;
        var idLocal = botao.getAttribute("data-id-local");
        var estaFavoritado = idsFavoritados.has(idLocal);
        var endpoint = estaFavoritado ? "../../php/favorito_remover.php" : "../../php/favorito_adicionar.php";

        botao.disabled = true;

        var formData = new FormData();
        formData.append("id_local", idLocal);

        fetch(endpoint, { method: "POST", body: formData })
        .then(function (response) {
            return response.json();
        })
        .then(function (retorno) {
            if (retorno.status === "ok") {
                if (estaFavoritado) {
                    idsFavoritados.delete(idLocal);
                    botao.textContent = "☆";
                    botao.setAttribute("aria-pressed", "false");
                    botao.setAttribute("title", "Adicionar aos favoritos");
                    botao.className = botao.className.replace("btn-warning", "btn-outline-secondary");
                } else {
                    idsFavoritados.add(idLocal);
                    botao.textContent = "★";
                    botao.setAttribute("aria-pressed", "true");
                    botao.setAttribute("title", "Remover dos favoritos");
                    botao.className = botao.className.replace("btn-outline-secondary", "btn-warning");
                }
            } else {
                alert(retorno.mensagem || "Erro ao atualizar favorito.");
            }
            botao.disabled = false;
        })
        .catch(function () {
            alert("Erro de conexao ao atualizar favorito.");
            botao.disabled = false;
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
                // Carrega os locais sem favoritos
                if (idInstituicao && /^\d+$/.test(idInstituicao)) {
                    carregarLocais(idInstituicao);
                }
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

            // Carrega favoritos e depois re-renderiza os locais com os botões corretos
            if (idInstituicao && /^\d+$/.test(idInstituicao)) {
                carregarFavoritosAluno(idInstituicao);
            }
        })
        .catch(function () {
            document.body.setAttribute("data-aluno-logado", "false");
            exibirLogoutAluno(false);
            if (idInstituicao && /^\d+$/.test(idInstituicao)) {
                carregarLocais(idInstituicao);
            }
        });
    }

    function exibirLogoutAluno(ativo) {
        alternarItemLogout(itemSeparadorLogoutAluno, ativo);
        alternarItemLogout(itemLogoutAluno, ativo);
        alternarItemLogout(itemFavoritosAluno, ativo);
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

    function escapeHtml(valor) {
        return String(valor === null || valor === undefined ? "" : valor)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function carregarFavoritos() {
        if (document.body.getAttribute("data-aluno-logado") === "false") {
            return;
        }

        fetch("../../php/favorito_get.php")
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            favoritosAluno = {};

            if (resposta.status === "ok" && Array.isArray(resposta.data)) {
                for (var i = 0; i < resposta.data.length; i++) {
                    favoritosAluno[String(resposta.data[i].id_local)] = true;
                }
            }

            atualizarResultadosBusca();
            atualizarPopupsAbertos();
        })
        .catch(function () {});
    }

    function alternarFavorito(idLocal) {
        if (document.body.getAttribute("data-aluno-logado") !== "true") {
            atualizarMapaStatus("Faca login como estudante para favoritar locais.", "warning");
            return;
        }

        var dados = new FormData();
        dados.append("id_local", idLocal);

        fetch("../../php/favorito_adicionar.php", {
            method: "POST",
            body: dados
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            if (resposta.status === "ok" && Array.isArray(resposta.data) && resposta.data.length > 0) {
                if (resposta.data[0].favorito) {
                    favoritosAluno[String(idLocal)] = true;
                } else {
                    delete favoritosAluno[String(idLocal)];
                }

                atualizarResultadosBusca();
                atualizarPopupsAbertos();
                atualizarMapaStatus(resposta.mensagem);
            } else {
                atualizarMapaStatus(resposta.mensagem || "Erro ao favoritar.", "danger");
            }
        })
        .catch(function () {
            atualizarMapaStatus("Erro de conexao ao favoritar.", "danger");
        });
    }

    function atualizarPopupsAbertos() {
        for (var id in marcadoresLocais) {
            if (marcadoresLocais[id] && marcadoresLocais[id].isPopupOpen()) {
                var local = buscarLocalPorId(id);
                if (local) {
                    marcadoresLocais[id].setPopupContent(montarPopupLocal(local));
                }
            }
        }
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

    if (buscaLocal) {
        buscaLocal.addEventListener("focus", function () {
            atualizarEstadoBuscaMapa(true);
        });

        buscaLocal.addEventListener("blur", function () {
            setTimeout(function () {
                atualizarEstadoBuscaMapa(buscaLocal.value.trim() !== "");
            }, 180);
        });

        buscaLocal.addEventListener("input", atualizarResultadosBusca);
    }

    if (btnLimparBusca) {
        btnLimparBusca.addEventListener("click", function () {
            if (buscaLocal) {
                buscaLocal.value = "";
                buscaLocal.focus();
            }
            atualizarEstadoBuscaMapa(true);
            atualizarResultadosBusca();
        });
    }

    if (resultadoLocais) {
        prepararRolagemResultados();

        resultadoLocais.addEventListener("click", function (e) {
            var botaoFav = e.target.closest("[data-favorito-local]");
            if (botaoFav) {
                e.stopPropagation();
                alternarFavorito(botaoFav.getAttribute("data-favorito-local"));
                return;
            }

            var botao = e.target.closest("[data-id-local]");
            if (!botao) {
                return;
            }

            selecionarLocal(botao.getAttribute("data-id-local"), true);
        });
    }

    if (mapaLocais) {
        mapaLocais.addEventListener("click", function (e) {
            var botaoFav = e.target.closest("[data-favorito-local]");
            if (botaoFav) {
                e.stopPropagation();
                alternarFavorito(botaoFav.getAttribute("data-favorito-local"));
                return;
            }

            var botao = e.target.closest("[data-rota-local]");
            if (!botao) {
                return;
            }

            selecionarLocal(botao.getAttribute("data-rota-local"), false);
        });
    }

    if (btnLocalizarEstudante) {
        btnLocalizarEstudante.addEventListener("click", localizarEstudante);
    }

    if (btnLimparRota) {
        btnLimparRota.addEventListener("click", limparRota);
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
