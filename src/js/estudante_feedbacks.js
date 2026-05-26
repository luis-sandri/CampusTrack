document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarEventosPnedentes();

    const formFeedback = document.getElementById("form-feedback");
    const modalFeedback = new bootstrap.Modal(document.getElementById("modalFeedback"));
    const idEventoInput = document.getElementById("id_evento_input");
    const nomeEventoModal = document.getElementById("nome_evento_modal");
    const modalAlert = document.getElementById("modal-alert");
    const btnEnviar = document.getElementById("btn-enviar");

    window.abrirModalFeedback = function(id_evento, nome_evento) {
        idEventoInput.value = id_evento;
        nomeEventoModal.textContent = nome_evento;
        formFeedback.reset();
        esconderAlertaModal();
        modalFeedback.show();
    };

    function mostrarAlertaModal(mensagem, tipo) {
        modalAlert.textContent = mensagem;
        modalAlert.className = `alert alert-${tipo}`;
    }

    function esconderAlertaModal() {
        modalAlert.className = "alert d-none";
    }

    formFeedback.addEventListener("submit", async (e) => {
        e.preventDefault();
        esconderAlertaModal();

        const comentario = document.getElementById("comentario_input").value.trim();

        if (comentario === "") {
            mostrarAlertaModal("Preencha os campos obrigatórios", "danger");
            return;
        }

        btnEnviar.disabled = true;
        btnEnviar.textContent = "Enviando...";

        const formData = new FormData(formFeedback);

        try {
            const response = await fetch("../../php/comentario_adicionar.php", {
                method: "POST",
                body: formData
            });
            const result = await response.json();

            if (result.status === "ok") {
                mostrarAlertaModal("Avaliação registrada com sucesso!", "success");
                setTimeout(() => {
                    modalFeedback.hide();
                    carregarEventosPnedentes(); // Recarrega a lista para remover o evento
                }, 1500);
            } else {
                mostrarAlertaModal(result.mensagem || "Erro ao salvar.", "danger");
            }
        } catch (error) {
            console.error(error);
            mostrarAlertaModal("Ocorreu um erro na requisição.", "danger");
        } finally {
            btnEnviar.disabled = false;
            btnEnviar.textContent = "Enviar Feedback";
        }
    });
});

async function carregarEventosPnedentes() {
    const listaEventos = document.getElementById("lista-eventos");
    const alertaEventos = document.getElementById("alerta-eventos");

    listaEventos.innerHTML = "";
    alertaEventos.className = "alert d-none";

    try {
        const response = await fetch("../../php/evento_pendente_avaliacao_get.php");
        const data = await response.json();

        if (data.status === "ok") {
            const eventos = data.data;

            if (eventos.length === 0) {
                alertaEventos.textContent = "Você não tem nenhum evento aguardando avaliação no momento.";
                alertaEventos.className = "alert alert-info";
                return;
            }

            eventos.forEach(evento => {
                const col = document.createElement("div");
                col.className = "col-12 col-md-6";

                col.innerHTML = `
                    <article class="card ct-event-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h2 class="card-title h5 text-primary mb-2">${evento.nome}</h2>
                            <p class="ct-event-meta mb-1">
                                <strong>Data:</strong> ${evento.data}
                            </p>
                            <p class="ct-event-meta mb-3">
                                <strong>Local:</strong> ${evento.nome_local}
                            </p>
                            <div class="mt-auto">
                                <button class="btn btn-outline-primary w-100" onclick="abrirModalFeedback(${evento.id_evento}, '${evento.nome.replace(/'/g, "\\'")}')">Avaliar Evento</button>
                            </div>
                        </div>
                    </article>
                `;
                listaEventos.appendChild(col);
            });
        } else {
            alertaEventos.textContent = data.mensagem;
            alertaEventos.className = "alert alert-danger";
        }
    } catch (error) {
        console.error(error);
        alertaEventos.textContent = "Erro ao carregar a lista de eventos.";
        alertaEventos.className = "alert alert-danger";
    }
}
