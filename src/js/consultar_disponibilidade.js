document.addEventListener('DOMContentLoaded', () => {
    valida_sessao();
    const formBusca = document.getElementById('form-busca');
    const dataInput = document.getElementById('data');
    const cardResultados = document.getElementById('card-resultados');
    const listaLocais = document.getElementById('lista-locais');
    const mensagemContainer = document.getElementById('mensagem-container');

    const modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    const detalheNome = document.getElementById('detalhe-nome');
    const detalheTipo = document.getElementById('detalhe-tipo');
    const detalheCapacidade = document.getElementById('detalhe-capacidade');
    const detalheStatus = document.getElementById('detalhe-status');

    let locaisData = [];

    formBusca.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const dataValor = dataInput.value;
        if (!dataValor) return;

        // O valor do input datetime-local vem como YYYY-MM-DDTHH:mm
        // O PHP espera algo que possa comparar com datetime, pode ser util trocar T por espaço:
        const dataFormatada = dataValor.replace('T', ' ') + ':00';

        mensagemContainer.innerHTML = '';
        cardResultados.classList.add('d-none');
        listaLocais.innerHTML = '';

        try {
            const response = await fetch(`../../php/local_disponibilidade_get.php?data=${encodeURIComponent(dataFormatada)}`);
            const data = await response.json();

            if (data.status === 'ok') {
                locaisData = data.data;

                if (locaisData.length === 0) {
                    mensagemContainer.innerHTML = `<div class="alert alert-warning">Nenhum local cadastrado no seu bloco.</div>`;
                    return;
                }

                let algumDisponivel = false;
                
                locaisData.forEach(local => {
                    const statusClass = local.status_disponibilidade === 'disponivel' ? 'status-disponivel' : 'status-ocupado';
                    const statusTexto = local.status_disponibilidade === 'disponivel' ? 'Disponível' : 'Ocupado';

                    if (local.status_disponibilidade === 'disponivel') {
                        algumDisponivel = true;
                    }

                    const tr = document.createElement('tr');
                    
                    // Somente abrir detalhes se disponivel (ou independentemente do status conforme critério 2: "visualizando a lista de espaços disponíveis, Seleciono um local")
                    // O critério diz "visualizando a lista de espaços disponíveis... Seleciono um local".
                    // Vamos deixar clicável os disponíveis.
                    if (local.status_disponibilidade === 'disponivel') {
                        tr.classList.add('clickable-row');
                        tr.addEventListener('click', () => mostrarDetalhes(local));
                    }
                    
                    tr.innerHTML = `
                        <td>${local.nome}</td>
                        <td>${local.tipo}</td>
                        <td class="${statusClass}">${statusTexto}</td>
                    `;
                    listaLocais.appendChild(tr);
                });

                cardResultados.classList.remove('d-none');

                if (!algumDisponivel) {
                    mensagemContainer.innerHTML = `<div class="alert alert-info">Nenhum espaço disponível para o período selecionado</div>`;
                }
            } else {
                mensagemContainer.innerHTML = `<div class="alert alert-danger">${data.mensagem}</div>`;
            }
        } catch (error) {
            console.error('Erro ao buscar disponibilidade:', error);
            mensagemContainer.innerHTML = `<div class="alert alert-danger">Ocorreu um erro ao buscar os dados.</div>`;
        }
    });

    function mostrarDetalhes(local) {
        detalheNome.textContent = local.nome;
        detalheTipo.textContent = local.tipo;
        detalheCapacidade.textContent = local.capacidade;
        
        const statusTexto = local.status_disponibilidade === 'disponivel' ? 'Disponível' : 'Ocupado';
        detalheStatus.textContent = statusTexto;
        
        detalheStatus.className = local.status_disponibilidade === 'disponivel' ? 'status-disponivel' : 'status-ocupado';

        modalDetalhes.show();
    }
});
