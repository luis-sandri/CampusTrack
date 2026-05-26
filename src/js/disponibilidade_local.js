document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();
});

document.getElementById("formBusca").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const dataInput = document.getElementById("data").value;
    const msgAlert = document.getElementById("mensagem");
    const cardResultados = document.getElementById("cardResultados");
    const listaResultados = document.getElementById("lista");
    
    msgAlert.classList.add("d-none");
    cardResultados.style.display = "none";
    
    if (!dataInput) {
        return;
    }

    const dataSelecionada = new Date(dataInput);
    const dataAtual = new Date();

    if (dataSelecionada <= dataAtual) {
        msgAlert.innerText = "Data ou horário inválidos. Informe um período futuro para realizar a consulta.";
        msgAlert.classList.remove("d-none");
        return;
    }

    const retorno = await fetch(`../../php/disponibilidade_local_get.php?data=${encodeURIComponent(dataInput)}`);
    const resposta = await retorno.json();

    if (resposta.status === "ok") {
        const registros = resposta.data;

        if (!Array.isArray(registros) || registros.length === 0) {
            msgAlert.innerText = "Nenhum espaço cadastrado neste bloco.";
            msgAlert.classList.remove("d-none");
            return;
        }

        const todosOcupados = registros.every(r => r.status_disponibilidade === 'Ocupado');
        
        if (todosOcupados) {
            msgAlert.innerText = "Nenhum espaço disponível para o período selecionado";
            msgAlert.classList.remove("d-none");
        } else {
            let html = `<table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Tipo</th>
                    <th>Capacidade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>`;

            registros.forEach(objeto => {
                const badgeClass = objeto.status_disponibilidade === 'Disponível' ? 'bg-success' : 'bg-danger';
                html += `<tr>
                    <td>${objeto.nome}</td>
                    <td>${objeto.tipo}</td>
                    <td>${objeto.capacidade}</td>
                    <td><span class="badge ${badgeClass}">${objeto.status_disponibilidade}</span></td>
                </tr>`;
            });

            html += "</tbody></table>";
            listaResultados.innerHTML = html;
            cardResultados.style.display = "block";
        }
    } else {
        alert("Erro: " + resposta.mensagem);
    }
});
