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
    var btnMenuEstudante = document.getElementById("btn-menu-estudante");
    var itemSeparadorLogoutAluno = document.getElementById("item-separador-logout-aluno");
    var itemLogoutAluno = document.getElementById("item-logout-aluno");
    var btnLogoutAluno = document.getElementById("btn-logout-aluno");
    var inputModo = document.getElementById("modo");
    var tituloEstudante = document.getElementById("titulo-estudante");
    var linksNavegacaoAluno = document.querySelectorAll(".ct-desktop-nav a, .ct-mobile-tabbar a");

    var urlParams = new URLSearchParams(window.location.search);
    var idInstituicao = urlParams.get("id_instituicao") || urlParams.get("id");
    var coordenadasPadrao = [-25.45275, -49.25083];
    var mapaLeaflet = null;
    var camadaLocais = null;
    var marcadoresLocais = {};
    var locaisMapa = [];
    var localSelecionado = null;
    var posicaoUsuario = null;
    var marcadorUsuario = null;
    var raioUsuario = null;
    var linhaRota = null;
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

    function carregarLocaisNoMapa(registros, mensagem, tipoMensagem) {
        locaisMapa = prepararLocaisMapa(registros);

        if (registros.length > 0 && registros[0].nome_instituicao) {
            var idInstituicaoTexto = document.getElementById("id-instituicao");
            if (idInstituicaoTexto) {
                idInstituicaoTexto.textContent = registros[0].nome_instituicao;
            }
        }

        if (locaisMapa.length === 0) {
            if (listaLocais) {
                listaLocais.innerHTML = '<div class="col-12 text-muted">Nenhum local cadastrado para esta instituicao.</div>';
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

    function prepararLocaisMapa(registros) {
        var locais = [];

        for (var i = 0; i < registros.length; i++) {
            var local = registros[i];
            var latitude = parseCoordenada(local.latitude);
            var longitude = parseCoordenada(local.longitude);

            if (coordenadaValida(latitude, longitude)) {
                locais.push({
                    id_local: String(local.id_local),
                    nome: local.nome || "",
                    tipo_escola: local.tipo_escola || "",
                    tipo: local.tipo || "",
                    capacidade: local.capacidade || "",
                    nome_instituicao: local.nome_instituicao || "",
                    fixo: local.fixo === true,
                    observacao: local.observacao || "",
                    latitude: latitude,
                    longitude: longitude
                });
            }
        }

        return locais;
    }

    function parseCoordenada(valor) {
        return Number(String(valor === null || valor === undefined ? "" : valor).replace(",", "."));
    }

    function coordenadaValida(latitude, longitude) {
        return Number.isFinite(latitude) && Number.isFinite(longitude) &&
            latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
    }

    function desenharLocaisNoMapa() {
        marcadoresLocais = {};

        if (!camadaLocais) {
            return;
        }

        camadaLocais.clearLayers();

        for (var i = 0; i < locaisMapa.length; i++) {
            adicionarMarcadorLocal(locaisMapa[i]);
        }
    }

    function adicionarMarcadorLocal(local) {
        var marcador = L.marker([local.latitude, local.longitude]);
        marcador.bindPopup(montarPopupLocal(local));
        marcador.on("click", function () {
            selecionarLocal(local.id_local, false);
        });
        marcador.addTo(camadaLocais);
        marcadoresLocais[local.id_local] = marcador;
    }

    function montarPopupLocal(local) {
        var html = '<strong>' + escapeHtml(local.nome) + '</strong>';
        html += '<div class="small text-muted">' + escapeHtml(local.tipo_escola) + ' - ' + escapeHtml(local.tipo) + '</div>';
        if (local.capacidade !== "") {
            html += '<div class="small">Capacidade: ' + escapeHtml(local.capacidade) + '</div>';
        }
        if (local.observacao !== "") {
            html += '<div class="small text-muted">' + escapeHtml(local.observacao) + '</div>';
        }
        html += '<button type="button" class="btn btn-primary btn-sm mt-2" data-rota-local="' + escapeHtml(local.id_local) + '">Tracar rota</button>';
        return html;
    }

    function ajustarMapaAosLocais() {
        if (!mapaLeaflet || locaisMapa.length === 0) {
            return;
        }

        var pontos = [];
        for (var i = 0; i < locaisMapa.length; i++) {
            pontos.push([locaisMapa[i].latitude, locaisMapa[i].longitude]);
        }

        var bounds = L.latLngBounds(pontos);

        if (locaisMapa.length === 1) {
            mapaLeaflet.setView(pontos[0], 18);
        } else {
            mapaLeaflet.fitBounds(bounds.pad(0.25));
            mapaLeaflet.setMaxBounds(bounds.pad(0.8));
        }
    }

    function montarListaLocais(locais) {
        var html = "";

        for (var i = 0; i < locais.length; i++) {
            html += '<div class="col-md-6">';
            html += '<div class="border rounded-3 p-3 h-100 bg-light">';
            html += '<h3 class="h6 fw-bold mb-1">' + escapeHtml(locais[i].nome) + '</h3>';
            html += '<p class="small text-muted mb-2">' + escapeHtml(locais[i].tipo_escola) + ' - ' + escapeHtml(locais[i].tipo) + '</p>';
            if (locais[i].observacao !== "") {
                html += '<p class="small text-muted mb-2">' + escapeHtml(locais[i].observacao) + '</p>';
            }
            html += '<p class="small mb-0">Capacidade: ' + escapeHtml(locais[i].capacidade) + '</p>';
            html += '<p class="small mb-0">Longitude: ' + escapeHtml(locais[i].longitude) + '</p>';
            html += '<p class="small mb-0">Latitude: ' + escapeHtml(locais[i].latitude) + '</p>';
            html += '</div>';
            html += '</div>';
        }

        return html;
    }

    function atualizarResultadosBusca() {
        if (!resultadoLocais) {
            return;
        }

        atualizarEstadoBuscaMapa();

        var termo = normalizarTexto(buscaLocal ? buscaLocal.value : "");
        var filtrados = [];

        for (var i = 0; i < locaisMapa.length; i++) {
            if (termo === "" || textoBuscaLocal(locaisMapa[i]).indexOf(termo) !== -1) {
                filtrados.push(locaisMapa[i]);
            }
        }

        if (locaisMapa.length === 0) {
            resultadoLocais.innerHTML = '<div class="text-muted py-2">Nenhum local disponivel.</div>';
            return;
        }

        if (filtrados.length === 0) {
            resultadoLocais.innerHTML = '<div class="text-muted py-2">Nenhum local encontrado.</div>';
            return;
        }

        var html = "";

        for (var j = 0; j < filtrados.length; j++) {
            var ativo = localSelecionado && localSelecionado.id_local === filtrados[j].id_local ? " is-active" : "";
            html += '<button type="button" class="ct-location-result' + ativo + '" data-id-local="' + escapeHtml(filtrados[j].id_local) + '">';
            html += '<span class="fw-semibold d-block">' + escapeHtml(filtrados[j].nome) + '</span>';
            html += '<span class="text-muted">' + escapeHtml(filtrados[j].tipo_escola) + ' - ' + escapeHtml(filtrados[j].tipo) + '</span>';
            html += '</button>';
        }

        resultadoLocais.innerHTML = html;
    }

    function prepararRolagemResultados() {
        if (!resultadoLocais) {
            return;
        }

        if (typeof L !== "undefined" && L.DomEvent) {
            L.DomEvent.disableScrollPropagation(resultadoLocais);
            L.DomEvent.disableClickPropagation(resultadoLocais);
        }

        var pararPropagacao = function (e) {
            e.stopPropagation();
        };

        resultadoLocais.addEventListener("wheel", pararPropagacao, { passive: true });
        resultadoLocais.addEventListener("touchstart", pararPropagacao, { passive: true });
        resultadoLocais.addEventListener("touchmove", pararPropagacao, { passive: true });
        resultadoLocais.addEventListener("pointerdown", pararPropagacao);
        resultadoLocais.addEventListener("pointermove", pararPropagacao);
    }

    function atualizarEstadoBuscaMapa(ativo) {
        if (!painelMapa) {
            return;
        }

        var buscaAtiva = typeof ativo === "boolean"
            ? ativo
            : buscaLocal && (buscaLocal.value.trim() !== "" || document.activeElement === buscaLocal);

        if (buscaAtiva) {
            painelMapa.classList.add("ct-search-open");
        } else {
            painelMapa.classList.remove("ct-search-open");
        }
    }

    function textoBuscaLocal(local) {
        return normalizarTexto(local.nome + " " + local.tipo_escola + " " + local.tipo);
    }

    function normalizarTexto(valor) {
        return String(valor === null || valor === undefined ? "" : valor)
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    }

    function selecionarLocal(idLocal, abrirPopup) {
        var local = buscarLocalPorId(idLocal);
        if (!local || !mapaLeaflet) {
            return;
        }

        localSelecionado = local;
        if (buscaLocal && window.matchMedia("(max-width: 767.98px)").matches) {
            buscaLocal.value = "";
        }
        atualizarEstadoBuscaMapa(false);
        if (buscaLocal) {
            buscaLocal.blur();
        }
        mapaLeaflet.setView([local.latitude, local.longitude], Math.max(mapaLeaflet.getZoom(), 18));

        if (marcadoresLocais[local.id_local] && abrirPopup !== false) {
            marcadoresLocais[local.id_local].openPopup();
        }

        atualizarResultadosBusca();

        if (posicaoUsuario) {
            desenharRotaAproximada(local);
        } else {
            atualizarMapaStatus("Destino selecionado. Use sua localizacao para tracar uma rota aproximada.");
        }
    }

    function buscarLocalPorId(idLocal) {
        var id = String(idLocal);

        for (var i = 0; i < locaisMapa.length; i++) {
            if (locaisMapa[i].id_local === id) {
                return locaisMapa[i];
            }
        }

        return null;
    }

    function localizarEstudante() {
        if (!mapaLeaflet) {
            atualizarMapaStatus("Aguarde o mapa carregar para usar sua localizacao.", "warning");
            return;
        }

        if (!navigator.geolocation) {
            atualizarMapaStatus("Seu navegador nao permite geolocalizacao.", "danger");
            return;
        }

        if (btnLocalizarEstudante) {
            btnLocalizarEstudante.disabled = true;
            btnLocalizarEstudante.textContent = "Localizando...";
        }

        if (watchLocalizacaoId !== null) {
            navigator.geolocation.clearWatch(watchLocalizacaoId);
        }

        watchLocalizacaoId = navigator.geolocation.watchPosition(
            function (posicao) {
                atualizarPosicaoUsuario(posicao);
                if (btnLocalizarEstudante) {
                    btnLocalizarEstudante.disabled = false;
                    btnLocalizarEstudante.textContent = "Atualizar minha localizacao";
                }
            },
            function () {
                if (btnLocalizarEstudante) {
                    btnLocalizarEstudante.disabled = false;
                    btnLocalizarEstudante.textContent = "Usar minha localizacao";
                }
                atualizarMapaStatus("Nao foi possivel obter sua localizacao. Verifique a permissao do navegador.", "danger");
            },
            {
                enableHighAccuracy: true,
                maximumAge: 10000,
                timeout: 15000
            }
        );
    }

    function solicitarLocalizacaoInicial() {
        if (localizacaoInicialSolicitada || !mapaLeaflet) {
            return;
        }

        localizacaoInicialSolicitada = true;
        localizarEstudante();
    }

    function atualizarPosicaoUsuario(posicao) {
        var latitude = posicao.coords.latitude;
        var longitude = posicao.coords.longitude;
        var precisao = posicao.coords.accuracy || 0;

        posicaoUsuario = {
            latitude: latitude,
            longitude: longitude,
            precisao: precisao
        };

        var coordenadas = [latitude, longitude];

        if (!marcadorUsuario) {
            marcadorUsuario = L.circleMarker(coordenadas, {
                className: "ct-user-location",
                radius: 8,
                fillColor: "#16a34a",
                fillOpacity: 1,
                color: "#ffffff",
                weight: 3
            }).addTo(mapaLeaflet).bindPopup("Voce esta aqui");
        } else {
            marcadorUsuario.setLatLng(coordenadas);
        }

        if (!raioUsuario) {
            raioUsuario = L.circle(coordenadas, {
                radius: precisao,
                color: "#16a34a",
                fillColor: "#16a34a",
                fillOpacity: 0.08,
                weight: 1
            }).addTo(mapaLeaflet);
        } else {
            raioUsuario.setLatLng(coordenadas);
            raioUsuario.setRadius(precisao);
        }

        if (localSelecionado) {
            desenharRotaAproximada(localSelecionado);
        } else {
            mapaLeaflet.setView(coordenadas, Math.max(mapaLeaflet.getZoom(), 18));
            atualizarMapaStatus("Localizacao encontrada. Selecione um destino para tracar a rota aproximada.");
        }
    }

    function desenharRotaAproximada(local) {
        if (!mapaLeaflet || !posicaoUsuario || !local) {
            return;
        }

        var origem = [posicaoUsuario.latitude, posicaoUsuario.longitude];
        var destino = [local.latitude, local.longitude];

        if (linhaRota) {
            mapaLeaflet.removeLayer(linhaRota);
        }

        linhaRota = L.polyline([origem, destino], {
            color: "#1d4ed8",
            dashArray: "8 8",
            opacity: 0.9,
            weight: 5
        }).addTo(mapaLeaflet);

        mapaLeaflet.fitBounds(L.latLngBounds([origem, destino]).pad(0.25));

        if (btnLimparRota) {
            btnLimparRota.disabled = false;
        }

        var distancia = distanciaMetros(posicaoUsuario.latitude, posicaoUsuario.longitude, local.latitude, local.longitude);
        atualizarMapaStatus("Rota aproximada ate " + local.nome + ": " + formatarDistancia(distancia) + ". O caminho real sera ajustado quando o grafo do campus for cadastrado.");
    }

    function limparRota() {
        if (linhaRota && mapaLeaflet) {
            mapaLeaflet.removeLayer(linhaRota);
        }

        linhaRota = null;
        localSelecionado = null;

        if (btnLimparRota) {
            btnLimparRota.disabled = true;
        }

        atualizarResultadosBusca();
        atualizarMapaStatus("Rota removida. Pesquise ou selecione outro destino.");
    }

    function distanciaMetros(lat1, lon1, lat2, lon2) {
        var raioTerra = 6371000;
        var dLat = grausParaRadianos(lat2 - lat1);
        var dLon = grausParaRadianos(lon2 - lon1);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(grausParaRadianos(lat1)) * Math.cos(grausParaRadianos(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return raioTerra * c;
    }

    function grausParaRadianos(valor) {
        return valor * Math.PI / 180;
    }

    function formatarDistancia(valor) {
        if (valor >= 1000) {
            return (valor / 1000).toFixed(1).replace(".", ",") + " km";
        }

        return Math.round(valor) + " m";
    }

    function atualizarMapaStatus(mensagem, tipo) {
        if (!mapaStatus) {
            return;
        }

        mapaStatus.textContent = mensagem || "";
        mapaStatus.className = "ct-map-status small mt-2 mb-0";

        if (tipo === "danger") {
            mapaStatus.classList.add("text-danger");
        } else if (tipo === "warning") {
            mapaStatus.classList.add("text-warning");
        } else {
            mapaStatus.classList.add("text-secondary");
        }
    }

    function atualizarLinksNavegacaoAluno(id) {
        if (!linksNavegacaoAluno || linksNavegacaoAluno.length === 0) {
            return;
        }

        var query = "?id=" + encodeURIComponent(id);

        for (var i = 0; i < linksNavegacaoAluno.length; i++) {
            var link = linksNavegacaoAluno[i];
            var aba = link.getAttribute("data-tab");

            if (aba === "mapa") {
                link.href = "instituicao.html" + query;
            } else {
                link.href = "../estudante/" + aba + ".html" + query;
            }
        }
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
            var botao = e.target.closest("[data-id-local]");
            if (!botao) {
                return;
            }

            selecionarLocal(botao.getAttribute("data-id-local"), true);
        });
    }

    if (mapaLocais) {
        mapaLocais.addEventListener("click", function (e) {
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
