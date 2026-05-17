/**
 * CampusTrack UI Module
 * Manages sidebar, tabs, toasts, loading states, and event rendering.
 */
const UI = (() => {
  const state = {
    activeTab: 'navigate',
    sidebarOpen: false,
  };

  function init() {
    setupTabs();
    setupMobileToggle();
    hideLoading();
  }

  function setupTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        const el = document.getElementById(`tab-${tab}`);
        if (el) el.classList.add('active');
        state.activeTab = tab;
      });
    });
  }

  function setupMobileToggle() {
    const toggle = document.getElementById('mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        state.sidebarOpen = sidebar.classList.contains('open');
      });
    }
  }

  function showLoading() {
    const el = document.getElementById('loading-overlay');
    if (el) { el.classList.remove('hidden'); }
  }

  function hideLoading() {
    const el = document.getElementById('loading-overlay');
    if (el) setTimeout(() => el.classList.add('hidden'), 600);
  }

  function toast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span class="toast-msg">${message}</span>`;
    container.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3500);
  }

  function showRouteResult(data) {
    const el = document.getElementById('route-result');
    if (!el) return;

    const userSteps = data.passos.filter(s => s.tipo !== 'corredor');

    el.innerHTML = `
      <div class="route-stats">
        <div class="stat-card"><div class="value">${data.distancia_total}</div><div class="label">Distância (un.)</div></div>
        <div class="stat-card"><div class="value">${userSteps.length}</div><div class="label">Paradas</div></div>
      </div>
      <div class="card-title">Passo a passo</div>
      <ul class="steps-list">
        ${data.passos.map((s, i) => `
          <li class="step-item">
            <div class="step-dot">${i + 1}</div>
            <div class="step-info">
              <div class="step-name">${s.nome}</div>
              <div class="step-type">${s.tipo}</div>
            </div>
          </li>
        `).join('')}
      </ul>
    `;
    el.classList.add('active');
  }

  function clearRouteResult() {
    const el = document.getElementById('route-result');
    if (el) { el.innerHTML = ''; el.classList.remove('active'); }
  }

  function setSelectionMode(active, text = '') {
    const el = document.getElementById('selection-mode');
    if (!el) return;
    if (active) {
      el.classList.add('active');
      el.querySelector('.text').textContent = text;
    } else {
      el.classList.remove('active');
    }
  }

  function updateRouteSlot(type, location) {
    const slot = document.getElementById(`slot-${type}`);
    if (!slot) return;
    if (location) {
      slot.classList.add('filled');
      slot.querySelector('.label').style.display = 'none';
      slot.querySelector('.name').textContent = location.nome;
      slot.querySelector('.name').style.display = 'block';
    } else {
      slot.classList.remove('filled');
      slot.querySelector('.label').style.display = 'block';
      slot.querySelector('.name').textContent = '';
      slot.querySelector('.name').style.display = 'none';
    }
  }

  function updateCalcButton(enabled) {
    const btn = document.getElementById('btn-calculate');
    if (btn) btn.disabled = !enabled;
  }

  return { init, showLoading, hideLoading, toast, showRouteResult, clearRouteResult, setSelectionMode, updateRouteSlot, updateCalcButton };
})();
