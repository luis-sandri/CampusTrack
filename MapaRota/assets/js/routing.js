/**
 * CampusTrack Routing Module
 * Manages origin/destination selection and route calculation state.
 */
const Routing = (() => {
  let origin = null;
  let destination = null;
  let selectionTarget = null; // 'origin' | 'destination' | null
  let currentRoute = null;

  function init() {
    setupSlotClicks();
    setupCalcButton();
    setupClearButtons();

    const clearRouteBtn = document.getElementById('btn-clear-route');
    if (clearRouteBtn) {
      clearRouteBtn.addEventListener('click', clearRouteOnly);
    }
  }

  function setupSlotClicks() {
    const originSlot = document.getElementById('slot-origin');
    const destSlot = document.getElementById('slot-destination');

    if (originSlot) {
      originSlot.addEventListener('click', () => startSelection('origin'));
    }
    if (destSlot) {
      destSlot.addEventListener('click', () => startSelection('destination'));
    }
  }

  function setupCalcButton() {
    const btn = document.getElementById('btn-calculate');
    if (btn) {
      btn.addEventListener('click', calculateRoute);
    }
  }

  function setupClearButtons() {
    document.querySelectorAll('.clear-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const slot = btn.closest('.route-slot');
        if (slot.id === 'slot-origin') clearOrigin();
        else clearDestination();
      });
    });
  }

  function startSelection(target) {
    selectionTarget = target;
    const text = target === 'origin'
      ? 'Clique em um ponto do mapa para definir a ORIGEM'
      : 'Clique em um ponto do mapa para definir o DESTINO';
    UI.setSelectionMode(true, text);
  }

  function handleNodeClick(location) {
    if (!selectionTarget) {
      // Auto-assign: if no origin, set origin; if no destination, set destination
      if (!origin) {
        setOrigin(location);
      } else if (!destination) {
        setDestination(location);
      } else {
        // Both set: replace origin, shift destination
        setOrigin(location);
      }
      return;
    }

    if (selectionTarget === 'origin') {
      setOrigin(location);
    } else {
      setDestination(location);
    }

    selectionTarget = null;
    UI.setSelectionMode(false);
  }

  function setOrigin(location) {
    origin = location;
    MapEngine.clearNodeStates();
    MapEngine.setNodeState(origin.id, 'origin');
    if (destination) MapEngine.setNodeState(destination.id, 'destination');
    UI.updateRouteSlot('origin', location);
    updateCalcState();
  }

  function setDestination(location) {
    destination = location;
    MapEngine.clearNodeStates();
    if (origin) MapEngine.setNodeState(origin.id, 'origin');
    MapEngine.setNodeState(destination.id, 'destination');
    UI.updateRouteSlot('destination', location);
    updateCalcState();
  }

  function clearOrigin() {
    origin = null;
    MapEngine.clearNodeStates();
    if (destination) MapEngine.setNodeState(destination.id, 'destination');
    UI.updateRouteSlot('origin', null);
    clearRoute();
    updateCalcState();
  }

  function clearDestination() {
    destination = null;
    MapEngine.clearNodeStates();
    if (origin) MapEngine.setNodeState(origin.id, 'origin');
    UI.updateRouteSlot('destination', null);
    clearRoute();
    updateCalcState();
  }

  function updateCalcState() {
    UI.updateCalcButton(origin !== null && destination !== null);
  }

  async function calculateRoute() {
    if (!origin || !destination) {
      UI.toast('Selecione origem e destino', 'error');
      return;
    }

    if (origin.id === destination.id) {
      UI.toast('Origem e destino são iguais', 'error');
      return;
    }

    const btn = document.getElementById('btn-calculate');
    const clearBtn = document.getElementById('btn-clear-route');
    const accessibleOnly = document.getElementById('accessibility-toggle')?.checked || false;

    btn.disabled = true;
    btn.textContent = 'Calculando...';
    document.getElementById('map-container').style.opacity = '0.7';

    try {
      // Pass the accessibility flag
      const result = await API.calcularRota(origin.id, destination.id, accessibleOnly);
      currentRoute = result;
      MapEngine.drawRoute(result.passos);
      UI.showRouteResult(result);
      if (clearBtn) clearBtn.style.display = 'block';
      UI.toast('Rota calculada com sucesso!', 'success');
    } catch (err) {
      UI.toast(err.message || 'Erro ao calcular rota', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = '📍 Calcular Rota';
      document.getElementById('map-container').style.opacity = '1';
    }
  }

  function clearRouteOnly() {
    currentRoute = null;
    MapEngine.clearRoute();
    UI.clearRouteResult();
    const clearBtn = document.getElementById('btn-clear-route');
    if (clearBtn) clearBtn.style.display = 'none';
  }

  function clearRoute() {
    currentRoute = null;
    MapEngine.clearRoute();
    UI.clearRouteResult();
    const clearBtn = document.getElementById('btn-clear-route');
    if (clearBtn) clearBtn.style.display = 'none';
  }

  function setDestinationById(localId, allLocations) {
    const loc = allLocations.find(l => l.id === localId);
    if (loc) {
      setDestination(loc);
      // Switch to navigate tab
      document.querySelector('[data-tab="navigate"]')?.click();
      UI.toast(`Destino: ${loc.nome}`, 'info');
    }
  }

  return { init, handleNodeClick, setDestinationById };
})();
