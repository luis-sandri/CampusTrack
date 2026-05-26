var mapa = null;
var camadaLocais = null;
var camadaNos = null;
var camadaArestas = null;
var instituicaoAtual = "";
var grafoAtual = { nos: [], arestas: [] };
var nosPorId = {};
var selecaoAresta = [];
var noSelecionadoId = "";

document.addEventListener("DOMContentLoaded", function () {
    valida_sessao();
    inicializarMapa();
    configurarEventos();
    carregarInstituicoes();
});

function inicializarMapa() {
    mapa = L.map("mapa-calibracao", {
        minZoom: 15
    }).setView([-25.45275, -49.25083], 17);

    mapa.attributionControl.setPrefix(false);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapa);

    camadaLocais = L.layerGroup().addTo(mapa);
    camadaArestas = L.layerGroup().addTo(mapa);
    camadaNos = L.layerGroup().addTo(mapa);

    mapa.on("click", function (event) {
        prepararNovoNo(event.latlng.lat, event.latlng.lng);
    });
}

function configurarEventos() {
    document.getElementById("instituicao").addEventListener("change", function () {
        instituicaoAtual = this.value;
        limparSelecaoAresta();
        carregarMapaInstituicao();
    });

    document.getElementById("form-no").addEventListener("submit", function (event) {
        event.preventDefault();
        salvarNo();
    });

    document.getElementById("btn-novo-no").addEventListener("click", function () {
        limparFormularioNo();
        desenharGrafo();
    });

    document.getElementById("btn-excluir-no").addEventListener("click", function () {
        excluirNo();
    });

    document.getElementById("btn-criar-aresta").addEventListener("click", function () {
        criarAresta();
    });

    document.getElementById("btn-limpar-selecao").addEventListener("click", function () {
        limparSelecaoAresta();
        desenharGrafo();
    });
}

async function carregarInstituicoes() {
    var retorno = await fetch("../../php/instituicao_get.php");
    var resposta = await retorno.json();

    if (resposta.status !== "ok" || !Array.isArray(resposta.data)) {
        alert("ERRO! Nao foi possivel carregar as instituicoes.");
        return;
    }

    var select = document.getElementById("instituicao");
    select.innerHTML = "";

    for (var i = 0; i < resposta.data.length; i++) {
        var item = resposta.data[i];
        select.innerHTML += '<option value="' + textoSeguro(item.id_instituicao) + '">' + textoSeguro(item.nome) + '</option>';
    }

    if (resposta.data.length > 0) {
        instituicaoAtual = String(resposta.data[0].id_instituicao);
        select.value = instituicaoAtual;
        carregarMapaInstituicao();
    }
}

async function carregarMapaInstituicao() {
    if (!instituicaoAtual) {
        return;
    }

    atualizarStatus("Carregando dados...");
    limparFormularioNo();

    await Promise.all([
        carregarLocais(),
        carregarGrafo()
    ]);

    atualizarStatus("Clique no mapa para criar pontos. Selecione um ponto para arrastar e clique em Salvar no para gravar.");
}

async function carregarLocais() {
    var retorno = await fetch("../../php/local_get.php?id_instituicao=" + encodeURIComponent(instituicaoAtual));
    var resposta = await retorno.json();

    camadaLocais.clearLayers();

    if (resposta.status !== "ok" || !Array.isArray(resposta.data)) {
        return;
    }

    var bounds = [];

    for (var i = 0; i < resposta.data.length; i++) {
        var local = resposta.data[i];
        var latitude = parseCoordenada(local.latitude);
        var longitude = parseCoordenada(local.longitude);

        if (!coordenadaValida(latitude, longitude)) {
            continue;
        }

        L.marker([latitude, longitude])
            .bindPopup("<strong>" + textoSeguro(local.nome) + "</strong><br><span class='small'>" + textoSeguro(local.tipo) + "</span>")
            .addTo(camadaLocais);
        bounds.push([latitude, longitude]);
    }

    if (bounds.length > 0) {
        mapa.fitBounds(L.latLngBounds(bounds).pad(0.25));
    }
}

async function carregarGrafo() {
    var retorno = await fetch("../../php/mapa_grafo_get.php?id_instituicao=" + encodeURIComponent(instituicaoAtual));
    var resposta = await retorno.json();

    if (resposta.status !== "ok" || !Array.isArray(resposta.data) || resposta.data.length === 0) {
        grafoAtual = { nos: [], arestas: [] };
    } else {
        grafoAtual = prepararGrafo(resposta.data[0]);
    }

    desenharGrafo();
}

function prepararGrafo(dados) {
    var nos = Array.isArray(dados.nos) ? dados.nos : [];
    var arestas = Array.isArray(dados.arestas) ? dados.arestas : [];

    nosPorId = {};
    for (var i = 0; i < nos.length; i++) {
        nosPorId[String(nos[i].id_no)] = nos[i];
    }

    return {
        nos: nos,
        arestas: arestas
    };
}

function desenharGrafo() {
    camadaNos.clearLayers();
    desenharArestasGrafo();

    for (var j = 0; j < grafoAtual.nos.length; j++) {
        desenharNo(grafoAtual.nos[j]);
    }
}

function desenharArestasGrafo() {
    camadaArestas.clearLayers();

    for (var i = 0; i < grafoAtual.arestas.length; i++) {
        desenharAresta(grafoAtual.arestas[i]);
    }
}

function desenharNo(no) {
    var latitude = parseCoordenada(no.latitude);
    var longitude = parseCoordenada(no.longitude);
    var id = String(no.id_no);

    if (!coordenadaValida(latitude, longitude)) {
        return;
    }

    var selecionadoParaAresta = selecaoAresta.indexOf(id) >= 0;
    var selecionadoParaEdicao = noSelecionadoId === id;
    var selecionado = selecionadoParaAresta || selecionadoParaEdicao;
    var tamanho = selecionado ? 20 : 16;
    var marcador = L.marker([latitude, longitude], {
        draggable: selecionadoParaEdicao,
        autoPan: true,
        icon: L.divIcon({
            className: "ct-map-node" + (selecionado ? " ct-node-selected" : ""),
            iconSize: [tamanho, tamanho],
            iconAnchor: [tamanho / 2, tamanho / 2],
            popupAnchor: [0, -tamanho / 2]
        })
    });

    marcador.bindPopup(
        "<strong>" + textoSeguro(no.nome) + "</strong><br>" +
        "<span class='small'>No #" + textoSeguro(id) + "</span>" +
        (selecionadoParaEdicao ? "<br><span class='small text-muted'>Arraste para ajustar e salve.</span>" : "")
    );

    marcador.on("click", function (event) {
        event.originalEvent.stopPropagation();
        selecionarNo(no);
    });

    marcador.on("drag", function (event) {
        atualizarPosicaoNo(no, event.target.getLatLng(), true);
    });

    marcador.on("dragend", function (event) {
        atualizarPosicaoNo(no, event.target.getLatLng(), true);
        atualizarStatus("Ponto ajustado. Clique em Salvar no para gravar a nova posicao.");
    });

    marcador.addTo(camadaNos);
}

function atualizarPosicaoNo(no, latlng, atualizarLinhas) {
    var id = String(no.id_no);
    var latitude = latlng.lat.toFixed(15);
    var longitude = latlng.lng.toFixed(15);

    no.latitude = latitude;
    no.longitude = longitude;
    nosPorId[id] = no;

    if (document.getElementById("no-id").value.trim() === id) {
        document.getElementById("no-latitude").value = latitude;
        document.getElementById("no-longitude").value = longitude;
    }

    if (atualizarLinhas) {
        desenharArestasGrafo();
    }
}

function desenharAresta(aresta) {
    var origem = nosPorId[String(aresta.id_no_origem)];
    var destino = nosPorId[String(aresta.id_no_destino)];

    if (!origem || !destino) {
        return;
    }

    var latOrigem = parseCoordenada(origem.latitude);
    var lonOrigem = parseCoordenada(origem.longitude);
    var latDestino = parseCoordenada(destino.latitude);
    var lonDestino = parseCoordenada(destino.longitude);

    if (!coordenadaValida(latOrigem, lonOrigem) || !coordenadaValida(latDestino, lonDestino)) {
        return;
    }

    var linha = L.polyline([[latOrigem, lonOrigem], [latDestino, lonDestino]], {
        color: "#1d4ed8",
        opacity: 0.8,
        weight: 4
    });

    linha.bindPopup(
        "<strong>Conexao #" + textoSeguro(aresta.id_aresta) + "</strong><br>" +
        "<span class='small'>" + textoSeguro(origem.nome) + " -> " + textoSeguro(destino.nome) + "</span><br>" +
        "<span class='small'>Distancia: " + textoSeguro(aresta.distancia_metros) + " m</span><br>" +
        "<button type='button' class='btn btn-danger btn-sm mt-2' data-excluir-aresta='" + textoSeguro(aresta.id_aresta) + "'>Excluir</button>"
    );

    linha.addTo(camadaArestas);
}

function selecionarNo(no) {
    var id = String(no.id_no);
    noSelecionadoId = id;
    preencherFormularioNo(no);

    var indice = selecaoAresta.indexOf(id);

    if (indice >= 0) {
        selecaoAresta.splice(indice, 1);
    } else {
        if (selecaoAresta.length >= 2) {
            selecaoAresta.shift();
        }
        selecaoAresta.push(id);
    }

    atualizarCamposAresta();
    atualizarStatus("Ponto selecionado. Arraste o marcador amarelo e clique em Salvar no para gravar.");
    desenharGrafo();
}

function prepararNovoNo(latitude, longitude) {
    noSelecionadoId = "";
    document.getElementById("no-id").value = "";
    document.getElementById("no-nome").value = "Novo ponto";
    document.getElementById("no-latitude").value = latitude.toFixed(15);
    document.getElementById("no-longitude").value = longitude.toFixed(15);
    document.getElementById("btn-excluir-no").disabled = true;
    atualizarStatus("Novo ponto preparado. Ajuste os dados e clique em Salvar no para cadastrar.");
    desenharGrafo();
}

function preencherFormularioNo(no) {
    noSelecionadoId = String(no.id_no);
    document.getElementById("no-id").value = no.id_no;
    document.getElementById("no-nome").value = no.nome;
    document.getElementById("no-latitude").value = no.latitude;
    document.getElementById("no-longitude").value = no.longitude;
    document.getElementById("btn-excluir-no").disabled = false;
}

function limparFormularioNo() {
    noSelecionadoId = "";
    document.getElementById("no-id").value = "";
    document.getElementById("no-nome").value = "";
    document.getElementById("no-latitude").value = "";
    document.getElementById("no-longitude").value = "";
    document.getElementById("btn-excluir-no").disabled = true;
}

async function salvarNo() {
    var id = document.getElementById("no-id").value.trim();
    var nome = document.getElementById("no-nome").value.trim();
    var latitude = document.getElementById("no-latitude").value.trim();
    var longitude = document.getElementById("no-longitude").value.trim();

    if (!instituicaoAtual || nome === "" || latitude === "" || longitude === "" || !isFinite(Number(latitude)) || !isFinite(Number(longitude))) {
        alert("ERRO! Informe nome, latitude e longitude validos.");
        return;
    }

    var dados = new FormData();
    dados.append("id_instituicao", instituicaoAtual);
    dados.append("nome", nome);
    dados.append("latitude", latitude);
    dados.append("longitude", longitude);

    var url = id === "" ? "../../php/mapa_no_adicionar.php" : "../../php/mapa_no_alterar.php?id=" + encodeURIComponent(id);
    var retorno = await fetch(url, { method: "POST", body: dados });
    var resposta = await retorno.json();

    if (resposta.status === "ok") {
        alert(resposta.mensagem);
        limparFormularioNo();
        await carregarGrafo();
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

async function excluirNo() {
    var id = document.getElementById("no-id").value.trim();

    if (id === "") {
        return;
    }

    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Excluir este no tambem remove as conexoes ligadas a ele.")
        : confirm("Excluir este no tambem remove as conexoes ligadas a ele.");

    if (!confirmado) {
        return;
    }

    var retorno = await fetch("../../php/mapa_no_excluir.php?id=" + encodeURIComponent(id) + "&id_instituicao=" + encodeURIComponent(instituicaoAtual));
    var resposta = await retorno.json();

    if (resposta.status === "ok") {
        alert(resposta.mensagem);
        limparFormularioNo();
        limparSelecaoAresta();
        await carregarGrafo();
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

function atualizarCamposAresta() {
    var origem = selecaoAresta[0] ? nosPorId[selecaoAresta[0]] : null;
    var destino = selecaoAresta[1] ? nosPorId[selecaoAresta[1]] : null;

    document.getElementById("aresta-origem").value = origem ? origem.id_no + " - " + origem.nome : "";
    document.getElementById("aresta-destino").value = destino ? destino.id_no + " - " + destino.nome : "";
    document.getElementById("btn-criar-aresta").disabled = !(origem && destino);
}

function limparSelecaoAresta() {
    selecaoAresta = [];
    atualizarCamposAresta();
}

async function criarAresta() {
    if (selecaoAresta.length !== 2) {
        return;
    }

    var dados = new FormData();
    dados.append("id_instituicao", instituicaoAtual);
    dados.append("id_no_origem", selecaoAresta[0]);
    dados.append("id_no_destino", selecaoAresta[1]);

    var retorno = await fetch("../../php/mapa_aresta_adicionar.php", {
        method: "POST",
        body: dados
    });
    var resposta = await retorno.json();

    if (resposta.status === "ok") {
        alert(resposta.mensagem);
        limparSelecaoAresta();
        await carregarGrafo();
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

async function excluirAresta(id) {
    var confirmado = typeof confirmarAcao === "function"
        ? await confirmarAcao("Excluir esta conexao?")
        : confirm("Excluir esta conexao?");

    if (!confirmado) {
        return;
    }

    var retorno = await fetch("../../php/mapa_aresta_excluir.php?id=" + encodeURIComponent(id) + "&id_instituicao=" + encodeURIComponent(instituicaoAtual));
    var resposta = await retorno.json();

    if (resposta.status === "ok") {
        alert(resposta.mensagem);
        await carregarGrafo();
    } else {
        alert("ERRO! " + resposta.mensagem);
    }
}

document.addEventListener("click", function (event) {
    var botao = event.target.closest("[data-excluir-aresta]");
    if (!botao) {
        return;
    }

    excluirAresta(botao.getAttribute("data-excluir-aresta"));
});

function parseCoordenada(valor) {
    return parseFloat(String(valor === null || valor === undefined ? "" : valor).replace(",", "."));
}

function coordenadaValida(latitude, longitude) {
    return Number.isFinite(latitude) && Number.isFinite(longitude) &&
        latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
}

function atualizarStatus(mensagem) {
    document.getElementById("mapa-status").textContent = mensagem || "";
}

function textoSeguro(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
