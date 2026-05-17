/**
 * CampusTrack Events UI Module
 * Renders upcoming events and handles their navigation actions.
 */
const EventsUI = (() => {
  function renderEvents(events) {
    const container = document.getElementById('events-list');
    if (!container) return;
    
    if (!events || !events.length) {
      container.innerHTML = '<p style="color:var(--text-muted);font-size:0.85rem;">Nenhum evento encontrado.</p>';
      return;
    }

    container.innerHTML = events.map(e => `
      <div class="event-card" data-local-id="${e.local_id}">
        <span class="event-type ${e.tipo}">${e.tipo}</span>
        <div class="event-title">${e.titulo}</div>
        <div class="event-location">📍 ${e.local_nome}</div>
        <div class="event-time">🕐 ${formatDate(e.data_inicio)}${e.data_fim ? ' — ' + formatDate(e.data_fim) : ''}</div>
        <button class="nav-btn" data-local-id="${e.local_id}">Navegar até aqui →</button>
      </div>
    `).join('');

    container.querySelectorAll('.nav-btn').forEach(btn => {
      btn.addEventListener('click', (ev) => {
        ev.stopPropagation();
        const localId = parseInt(btn.dataset.localId);
        if (window.CampusTrack) window.CampusTrack.setDestinationById(localId);
      });
    });
  }

  function formatDate(str) {
    const d = new Date(str);
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
  }

  return { renderEvents };
})();
