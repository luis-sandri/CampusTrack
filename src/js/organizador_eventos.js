document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarMeusEventos();
});

async function carregarMeusEventos() {
    const listaEventos = document.getElementById("lista-meus-eventos");
    const alertaEventos = document.getElementById("alerta-meus-eventos");

    listaEventos.innerHTML = "<div class='col-12 text-center'>Carregando eventos...</div>";
    alertaEventos.className = "alert d-none";

    try {
        const response = await fetch("../../php/organizador_meus_eventos_get.php");
        const data = await response.json();

        if (data.status === "ok") {
            const eventos = data.data;

            if (eventos.length === 0) {
                listaEventos.innerHTML = "";
                alertaEventos.textContent = "Você ainda não cadastrou nenhum evento.";
                alertaEventos.className = "alert alert-info";
                return;
            }

            let html = "";
            eventos.forEach(evento => {
                let badgeClass = "bg-secondary";
                let badgeText = evento.status.toUpperCase();
                let botoesAcoes = "";

                if (evento.status === "ativo") {
                    badgeClass = "bg-success";
                    botoesAcoes = `
                        <div class="mt-3 border-top pt-3 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-danger" onclick="alterarStatus(${evento.id_evento}, 'encerrado')">Encerrar Evento</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="alterarStatus(${evento.id_evento}, 'cancelado')">Cancelar Evento</button>
                        </div>
                    `;
                } else if (evento.status === "encerrado") {
                    badgeClass = "bg-primary";
                } else if (evento.status === "cancelado") {
                    badgeClass = "bg-danger";
                }

                html += `
                    <article class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h2 class="card-title h5 text-dark mb-0">${textoSeguro(evento.nome)}</h2>
                                    <span class="badge ${badgeClass}">${badgeText}</span>
                                </div>
                                <p class="text-secondary small mb-1">
                                    <strong>Data:</strong> ${evento.data_formatada || evento.data}
                                </p>
                                <p class="text-secondary small mb-0">
                                    <strong>Local:</strong> ${textoSeguro(evento.nome_local)}
                                </p>
                                ${botoesAcoes}
                            </div>
                        </div>
                    </article>
                `;
            });
            listaEventos.innerHTML = html;
        } else {
            listaEventos.innerHTML = "";
            alertaEventos.textContent = data.mensagem;
            alertaEventos.className = "alert alert-danger";
        }
    } catch (error) {
        console.error(error);
        listaEventos.innerHTML = "";
        alertaEventos.textContent = "Erro ao carregar os eventos.";
        alertaEventos.className = "alert alert-danger";
    }
}

async function alterarStatus(id_evento, novo_status) {
    let acao = novo_status === 'encerrado' ? 'encerrar' : 'cancelar';
    if (!confirm(`Tem certeza que deseja ${acao} este evento?`)) return;

    try {
        const formData = new FormData();
        formData.append("id_evento", id_evento);
        formData.append("status", novo_status);

        const response = await fetch("../../php/evento_alterar_status.php", {
            method: "POST",
            body: formData
        });
        const data = await response.json();

        if (data.status === "ok") {
            carregarMeusEventos(); // recarrega a lista
        } else {
            alert(data.mensagem);
        }
    } catch (error) {
        console.error(error);
        alert("Erro ao alterar o status do evento.");
    }
}

function textoSeguro(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
