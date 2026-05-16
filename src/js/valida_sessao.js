var sessaoValidacaoPromise = null;

function obterPerfilEsperado() {
    return document.body ? document.body.getAttribute("data-perfil") : "";
}

function obterLoginPorPerfil(perfil) {
    var rotas = {
        admin: "login.html",
        gerente: "login.html",
        organizacao: "login.html?tipo=organizacao",
        organizador: "organizador_login.html",
        aluno: "login.html"
    };

    return rotas[perfil] || "../index.html";
}

function tipoMensagem(mensagem) {
    var texto = String(mensagem || "").toLowerCase();

    if (texto.indexOf("sucesso") >= 0 || texto.indexOf("validado") >= 0) {
        return "success";
    }

    if (texto.indexOf("aviso") >= 0 || texto.indexOf("aten") >= 0) {
        return "warning";
    }

    return "danger";
}

function mostrarMensagem(mensagem, tipo) {
    var container = document.querySelector(".container.mt-5") || document.querySelector("main.container") || document.querySelector(".container");

    if (!container) {
        window.__alertaOriginal(mensagem);
        return;
    }

    var alerta = document.getElementById("alerta-global");

    if (!alerta) {
        alerta = document.createElement("div");
        alerta.id = "alerta-global";
        alerta.setAttribute("role", "alert");
        container.insertBefore(alerta, container.firstChild);
    }

    alerta.textContent = mensagem;
    alerta.className = "alert alert-" + (tipo || tipoMensagem(mensagem)) + " w-100";
}

function confirmarAcao(mensagem) {
    if (!window.bootstrap) {
        return Promise.resolve(window.__confirmOriginal(mensagem));
    }

    return new Promise(function (resolve) {
        var modal = document.getElementById("modal-confirmacao-global");

        if (!modal) {
            modal = document.createElement("div");
            modal.id = "modal-confirmacao-global";
            modal.className = "modal fade";
            modal.tabIndex = -1;
            modal.innerHTML =
                '<div class="modal-dialog modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title">Confirmar acao</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>' +
                        '</div>' +
                        '<div class="modal-body"><p class="mb-0" id="modal-confirmacao-mensagem"></p></div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>' +
                            '<button type="button" class="btn btn-danger" id="modal-confirmacao-ok">Confirmar</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(modal);
        }

        document.getElementById("modal-confirmacao-mensagem").textContent = mensagem;

        var confirmado = false;
        var modalBootstrap = new bootstrap.Modal(modal);
        var botaoConfirmar = document.getElementById("modal-confirmacao-ok");

        botaoConfirmar.onclick = function () {
            confirmado = true;
            modalBootstrap.hide();
        };

        modal.addEventListener("hidden.bs.modal", function aoFechar() {
            modal.removeEventListener("hidden.bs.modal", aoFechar);
            resolve(confirmado);
        });

        modalBootstrap.show();
    });
}

function textoSeguro(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function coletarItensMenu(navContainer) {
    var itens = [];
    var grupos = Array.prototype.slice.call(navContainer.querySelectorAll(".btn-group"));

    grupos.forEach(function (grupo) {
        var botao = grupo.querySelector(".dropdown-toggle");

        if (!botao || botao.textContent.trim().toLowerCase() !== "menu") {
            return;
        }

        var links = Array.prototype.slice.call(grupo.querySelectorAll(".dropdown-menu .dropdown-item"));
        links.forEach(function (link) {
            itens.push({
                href: link.getAttribute("href") || "#",
                texto: link.textContent.trim(),
                ativo: link.classList.contains("active")
            });
        });

        grupo.remove();
    });

    return itens;
}

function montarAreaUsuario(dados) {
    var navContainer = document.querySelector(".navbar .container-fluid") || document.querySelector(".navbar .container");

    if (!navContainer) {
        var nav = document.createElement("nav");
        nav.className = "navbar bg-body-tertiary border-bottom";
        nav.innerHTML =
            '<div class="container-fluid">' +
                '<a class="navbar-brand fw-bold text-primary fs-4" href="../index.html">CT</a>' +
            '</div>';
        document.body.insertBefore(nav, document.body.firstChild);
        navContainer = nav.querySelector(".container-fluid");
    }

    var itensMenu = coletarItensMenu(navContainer);
    var usuarioExistente = document.getElementById("usuario-email");
    var areaExistente = document.querySelector(".ct-user-actions");

    if (usuarioExistente && usuarioExistente.closest(".d-flex")) {
        usuarioExistente.closest(".d-flex").remove();
    }

    if (areaExistente) {
        areaExistente.remove();
    }

    var area = document.createElement("div");
    area.className = "ct-user-actions btn-group dropstart ms-auto";

    var htmlItens = "";
    for (var i = 0; i < itensMenu.length; i++) {
        htmlItens +=
            '<li><a class="dropdown-item' + (itensMenu[i].ativo ? " active" : "") + '" href="' + textoSeguro(itensMenu[i].href) + '">' +
                textoSeguro(itensMenu[i].texto) +
            '</a></li>';
    }

    if (htmlItens !== "") {
        htmlItens += '<li><hr class="dropdown-divider"></li>';
    }

    area.innerHTML =
        '<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' +
            textoSeguro(dados.nome) +
        '</button>' +
        '<ul class="dropdown-menu">' +
            '<li><h6 class="dropdown-header">' + textoSeguro(dados.perfil) + '</h6></li>' +
            htmlItens +
            '<li><button type="button" class="dropdown-item text-danger ct-btn-sair">Sair</button></li>' +
        '</ul>';

    navContainer.appendChild(area);
}

function configurarLogout(perfil) {
    var botoes = Array.prototype.slice.call(document.querySelectorAll(".ct-btn-sair, a, button"));

    botoes.forEach(function (botao) {
        if (botao.getAttribute("data-logout-configurado") === "true") {
            return;
        }

        if (botao.classList.contains("ct-btn-sair") || botao.textContent.trim().toLowerCase() === "sair") {
            botao.setAttribute("data-logout-configurado", "true");
            botao.addEventListener("click", function (event) {
                event.preventDefault();

                fetch("../../php/logout.php")
                    .then(function () {
                        window.location.href = obterLoginPorPerfil(perfil);
                    })
                    .catch(function () {
                        window.location.href = obterLoginPorPerfil(perfil);
                    });
            });
        }
    });
}

function valida_sessao() {
    var perfil = obterPerfilEsperado();

    if (!perfil) {
        return Promise.resolve(true);
    }

    if (sessaoValidacaoPromise) {
        return sessaoValidacaoPromise;
    }

    sessaoValidacaoPromise = fetch("../../php/sessao_status.php?perfil=" + encodeURIComponent(perfil))
        .then(function (response) {
            return response.json();
        })
        .then(function (resposta) {
            if (resposta.status !== "ok" || !Array.isArray(resposta.data) || resposta.data.length === 0) {
                window.location.href = obterLoginPorPerfil(perfil);
                return false;
            }

            montarAreaUsuario(resposta.data[0]);
            configurarLogout(perfil);
            return true;
        })
        .catch(function () {
            window.location.href = obterLoginPorPerfil(perfil);
            return false;
        });

    return sessaoValidacaoPromise;
}

window.__alertaOriginal = window.__alertaOriginal || window.alert.bind(window);
window.__confirmOriginal = window.__confirmOriginal || window.confirm.bind(window);
window.alert = function (mensagem) {
    mostrarMensagem(mensagem, tipoMensagem(mensagem));
};

document.addEventListener("DOMContentLoaded", function () {
    if (obterPerfilEsperado()) {
        valida_sessao();
    }
});
