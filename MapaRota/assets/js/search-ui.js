/**
 * CampusTrack Search UI Module
 * Renders search results and handles selection.
 */
const SearchUI = (() => {
  function renderSearchResults(results) {
    const container = document.getElementById('search-results');
    if (!container) return;
    
    if (!results || !results.length) {
      container.innerHTML = '<p style="color:var(--text-muted);font-size:0.85rem;padding:10px;">Nenhum local encontrado.</p>';
      return;
    }

    container.innerHTML = results.map(r => `
      <div class="route-slot" style="margin-bottom:8px; cursor:pointer;" data-local-id="${r.id}">
        <div class="icon" style="background:rgba(255,255,255,0.1)">📍</div>
        <div>
          <div class="name" style="display:block;">${r.nome}</div>
          <div class="label" style="display:block;">${r.tipo} ${r.edificio_nome ? '— ' + r.edificio_nome : ''}</div>
        </div>
      </div>
    `).join('');

    container.querySelectorAll('.route-slot').forEach(slot => {
      slot.addEventListener('click', () => {
        const localId = parseInt(slot.dataset.localId);
        if (window.CampusTrack) window.CampusTrack.setDestinationById(localId);
      });
    });
  }

  return { renderSearchResults };
})();
