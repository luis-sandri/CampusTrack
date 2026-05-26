document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
    carregarResumoFeedbacks();
});

async function carregarResumoFeedbacks() {
    const listaFeedbacks = document.getElementById("lista-feedbacks");
    const alertaFeedbacks = document.getElementById("alerta-feedbacks");

    listaFeedbacks.innerHTML = "";
    alertaFeedbacks.className = "alert d-none";

    try {
        const response = await fetch("../../php/comentario_resumo_organizador_get.php");
        const data = await response.json();

        if (data.status === "ok") {
            const eventos = data.data;

            if (eventos.length === 0) {
                alertaFeedbacks.textContent = "Você não tem eventos encerrados com feedbacks.";
                alertaFeedbacks.className = "alert alert-info";
                return;
            }

            eventos.forEach(evento => {
                const col = document.createElement("div");
                col.className = "col-12 col-md-6";

                let comentariosHtml = "";
                if (evento.total_comentarios === 0) {
                    comentariosHtml = "<p class='text-muted small'>Nenhum comentário recebido ainda.</p>";
                } else {
                    comentariosHtml = `<ul class="list-group list-group-flush border-top mt-3 pt-2">`;
                    evento.comentarios.forEach(c => {
                        const cursoStr = c.curso ? `(${c.curso})` : "";
                        comentariosHtml += `<li class="list-group-item bg-transparent px-0 border-0">
                            <blockquote class="blockquote mb-0 fs-6">
                              <p class="mb-1 text-dark">"${textoSeguro(c.texto)}"</p>
                              <footer class="blockquote-footer mt-0">${cursoStr}</footer>
                            </blockquote>
                        </li>`;
                    });
                    comentariosHtml += `</ul>`;
                }

                col.innerHTML = `
                    <article class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="card-title h5 text-primary mb-2">${textoSeguro(evento.nome_evento)}</h2>
                            <p class="text-secondary small mb-3">
                                <strong>Data:</strong> ${evento.data} <br>
                                <strong>Total de Feedbacks:</strong> ${evento.total_comentarios}
                            </p>
                            ${comentariosHtml}
                        </div>
                    </article>
                `;
                listaFeedbacks.appendChild(col);
            });
        } else {
            alertaFeedbacks.textContent = data.mensagem;
            alertaFeedbacks.className = "alert alert-danger";
        }
    } catch (error) {
        console.error(error);
        alertaFeedbacks.textContent = "Erro ao carregar a lista de feedbacks.";
        alertaFeedbacks.className = "alert alert-danger";
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
