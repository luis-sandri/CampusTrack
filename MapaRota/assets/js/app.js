/**
 * CampusTrack Application Entry Point
 * Bootstraps all modules and orchestrates initialization.
 */
const CampusTrack = (() => {
  let allLocations = [];

  async function init() {
    try {
      // Initialize map engine with node click handler
      MapEngine.init({ onNodeClick: (loc) => Routing.handleNodeClick(loc) });

      // Initialize UI
      UI.init();

      // Initialize routing module
      Routing.init();

      // Load data from API
      await loadMapData();
      await loadEvents();

      // Setup map controls
      setupControls();

      // Setup search
      setupSearch();

    } catch (err) {
      console.error('Init error:', err);
      UI.toast('Erro ao inicializar o sistema', 'error');
      UI.hideLoading();
    }
  }

  async function loadMapData() {
    try {
      const data = await API.getMapData();
      allLocations = [...data.locations, ...data.waypoints];

      if (data.locations.length === 0) {
        UI.toast('Nenhum local cadastrado', 'info');
      }

      // Only render non-waypoint locations as clickable nodes
      MapEngine.renderNodes(data.locations);
    } catch (err) {
      console.error('Map data error:', err);
      UI.toast('Erro ao carregar dados do mapa', 'error');
    }
  }

  async function loadEvents() {
    try {
      const events = await API.getProxEventos(10);
      EventsUI.renderEvents(events);
    } catch (err) {
      console.error('Events error:', err);
    }
  }

  function setupControls() {
    document.getElementById('btn-zoom-in')?.addEventListener('click', () => MapEngine.zoomIn());
    document.getElementById('btn-zoom-out')?.addEventListener('click', () => MapEngine.zoomOut());
    document.getElementById('btn-reset-view')?.addEventListener('click', () => MapEngine.resetView());
  }

  function setupSearch() {
    const input = document.getElementById('search-input');
    if (!input) return;

    let timeout = null;
    input.addEventListener('input', (e) => {
      clearTimeout(timeout);
      const query = e.target.value.trim();
      
      if (query.length < 2) {
        SearchUI.renderSearchResults([]);
        return;
      }

      timeout = setTimeout(async () => {
        try {
          const results = await API.searchLocais(query);
          SearchUI.renderSearchResults(results);
        } catch (err) {
          console.error('Search error:', err);
        }
      }, 300);
    });
  }

  function setDestinationById(localId) {
    Routing.setDestinationById(localId, allLocations);
  }

  return { init, setDestinationById };
})();

// Boot
document.addEventListener('DOMContentLoaded', () => CampusTrack.init());
