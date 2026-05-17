<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="CampusTrack — Sistema de navegação indoor para campus universitário. Encontre rotas, salas e eventos.">
  <title>CampusTrack — Navegação Inteligente</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Loading Overlay -->
  <div id="loading-overlay" class="loading-overlay">
    <div class="loader"></div>
    <div class="loading-text">Carregando CampusTrack...</div>
  </div>

  <!-- Toast Container -->
  <div id="toast-container" class="toast-container"></div>

  <!-- Mobile Toggle -->
  <button id="mobile-toggle" class="mobile-toggle" aria-label="Menu">☰</button>

  <!-- App Layout -->
  <div class="app-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo">CT</div>
        <h1>CampusTrack</h1>
        <span class="version">v2.0</span>
      </div>

      <div class="sidebar-body">
        <!-- Tabs Navigation -->
        <div class="tab-nav">
          <button class="tab-btn active" data-tab="navigate">Navegar</button>
          <button class="tab-btn" data-tab="events">Eventos</button>
          <button class="tab-btn" data-tab="search">Buscar</button>
        </div>

        <!-- Tab: Navigate -->
        <div id="tab-navigate" class="tab-content active">
          <!-- Selection Mode Indicator -->
          <div id="selection-mode" class="selection-mode">
            <div class="dot"></div>
            <span class="text"></span>
          </div>

          <!-- Route Slots -->
          <div class="card">
            <div class="card-title">Rota</div>

            <div id="slot-origin" class="route-slot origin">
              <div class="icon">▲</div>
              <div>
                <div class="label">Clique para definir origem</div>
                <div class="name" style="display:none;"></div>
              </div>
              <button class="clear-btn" title="Limpar">✕</button>
            </div>

            <div id="slot-destination" class="route-slot destination">
              <div class="icon">▼</div>
              <div>
                <div class="label">Clique para definir destino</div>
                <div class="name" style="display:none;"></div>
              </div>
              <button class="clear-btn" title="Limpar">✕</button>
            </div>

            <div style="margin: 15px 0; display: flex; align-items: center; justify-content: space-between;">
              <label style="display:flex; align-items:center; cursor:pointer; font-size: 0.85rem; color: var(--text-muted);">
                <input type="checkbox" id="accessibility-toggle" style="margin-right: 8px;">
                Rota Acessível (Sem Escadas)
              </label>
            </div>

            <div style="display:flex; gap: 10px;">
              <button id="btn-calculate" class="btn btn-primary" style="flex:1;" disabled>
                📍 Calcular Rota
              </button>
              <button id="btn-clear-route" class="btn btn-secondary" style="display:none;" title="Limpar Rota">
                Limpar Rota
              </button>
            </div>
          </div>

          <!-- Route Result -->
          <div id="route-result" class="route-result"></div>
        </div>

        <!-- Tab: Events -->
        <div id="tab-events" class="tab-content">
          <div class="card">
            <div class="card-title">Próximos Eventos</div>
            <div id="events-list"></div>
          </div>
        </div>

        <!-- Tab: Search -->
        <div id="tab-search" class="tab-content">
          <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="search-input" placeholder="Buscar local...">
          </div>
          <div id="search-results"></div>
        </div>

      </div>
    </aside>

    <!-- Map Area -->
    <main class="map-area">
      <div id="map-container" class="map-container">
        <div id="map-inner" class="map-inner">
          <img id="map-image" src="mapa.png" alt="Mapa do Campus" draggable="false">
        </div>
      </div>

      <!-- Map Controls -->
      <div class="map-controls">
        <button id="btn-zoom-in" class="map-ctrl-btn" title="Zoom In">+</button>
        <button id="btn-zoom-out" class="map-ctrl-btn" title="Zoom Out">−</button>
        <button id="btn-reset-view" class="map-ctrl-btn" title="Reset View">⟲</button>
      </div>
    </main>

  </div>

  <!-- Scripts (modular, order matters) -->
  <script src="assets/js/api.js"></script>
  <script src="assets/js/ui.js"></script>
  <script src="assets/js/events-ui.js"></script>
  <script src="assets/js/search-ui.js"></script>
  <script src="assets/js/map.js"></script>
  <script src="assets/js/routing.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
