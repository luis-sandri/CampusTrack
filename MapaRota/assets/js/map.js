/**
 * CampusTrack Map Engine
 * Handles map rendering, pan/zoom, node placement, and SVG route drawing.
 */
const MapEngine = (() => {
  let container, inner, img, svgOverlay;
  let nodes = [];
  let scale = 1, panX = 0, panY = 0;
  let isDragging = false, dragStart = { x: 0, y: 0 }, panStart = { x: 0, y: 0 };
  let imgNatW = 0, imgNatH = 0;
  let onNodeClick = null;

  function init(options = {}) {
    container = document.getElementById('map-container');
    inner = document.getElementById('map-inner');
    img = document.getElementById('map-image');
    
    if (img) {
      img.onerror = () => {
        if (window.UI) UI.toast('Erro ao carregar o mapa', 'error');
      };
    }
    
    onNodeClick = options.onNodeClick || null;

    createSvgOverlay();
    setupPanZoom();

    img.onload = () => {
      imgNatW = img.naturalWidth;
      imgNatH = img.naturalHeight;
      fitToContainer();
    };

    if (img.complete && img.naturalWidth) {
      imgNatW = img.naturalWidth;
      imgNatH = img.naturalHeight;
      fitToContainer();
    }

    window.addEventListener('resize', () => fitToContainer());
  }

  function createSvgOverlay() {
    svgOverlay = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svgOverlay.classList.add('route-svg');
    svgOverlay.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    inner.appendChild(svgOverlay);
  }

  function setupPanZoom() {
    container.addEventListener('mousedown', onDragStart);
    container.addEventListener('mousemove', onDragMove);
    container.addEventListener('mouseup', onDragEnd);
    container.addEventListener('mouseleave', onDragEnd);
    container.addEventListener('wheel', onWheel, { passive: false });

    container.addEventListener('touchstart', onTouchStart, { passive: false });
    container.addEventListener('touchmove', onTouchMove, { passive: false });
    container.addEventListener('touchend', onDragEnd);
  }

  function onDragStart(e) {
    if (e.target.closest('.map-node')) return;
    isDragging = true;
    dragStart = { x: e.clientX, y: e.clientY };
    panStart = { x: panX, y: panY };
  }

  function onDragMove(e) {
    if (!isDragging) return;
    panX = panStart.x + (e.clientX - dragStart.x);
    panY = panStart.y + (e.clientY - dragStart.y);
    applyTransform();
  }

  function onDragEnd() { isDragging = false; }

  let lastTouchDist = 0;
  function onTouchStart(e) {
    if (e.touches.length === 1) {
      isDragging = true;
      dragStart = { x: e.touches[0].clientX, y: e.touches[0].clientY };
      panStart = { x: panX, y: panY };
    } else if (e.touches.length === 2) {
      lastTouchDist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
    }
  }

  function onTouchMove(e) {
    e.preventDefault();
    if (e.touches.length === 1 && isDragging) {
      panX = panStart.x + (e.touches[0].clientX - dragStart.x);
      panY = panStart.y + (e.touches[0].clientY - dragStart.y);
      applyTransform();
    } else if (e.touches.length === 2) {
      const dist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      const delta = dist - lastTouchDist;
      zoom(delta > 0 ? 0.02 : -0.02);
      lastTouchDist = dist;
    }
  }

  function onWheel(e) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    zoom(delta, e.clientX, e.clientY);
  }

  function zoom(delta, cx, cy) {
    const oldScale = scale;
    scale = Math.max(0.3, Math.min(3, scale + delta));
    if (cx !== undefined && cy !== undefined) {
      const rect = container.getBoundingClientRect();
      const mx = cx - rect.left, my = cy - rect.top;
      panX = mx - (mx - panX) * (scale / oldScale);
      panY = my - (my - panY) * (scale / oldScale);
    }
    applyTransform();
  }

  function applyTransform() {
    inner.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
  }

  function fitToContainer() {
    if (!imgNatW || !imgNatH) return;
    const cw = container.clientWidth, ch = container.clientHeight;
    scale = Math.min(cw / imgNatW, ch / imgNatH) * 0.95;
    panX = (cw - imgNatW * scale) / 2;
    panY = (ch - imgNatH * scale) / 2;
    applyTransform();
    updateSvgSize();
  }

  function updateSvgSize() {
    svgOverlay.setAttribute('width', imgNatW);
    svgOverlay.setAttribute('height', imgNatH);
    svgOverlay.setAttribute('viewBox', `0 0 ${imgNatW} ${imgNatH}`);
  }

  function renderNodes(locations) {
    inner.querySelectorAll('.map-node').forEach(n => n.remove());
    nodes = locations;

    locations.forEach(loc => {
      const el = document.createElement('div');
      el.className = `map-node type-${loc.tipo}`;
      el.dataset.id = loc.id;
      el.style.left = `${(loc.x / 100) * imgNatW}px`;
      el.style.top = `${(loc.y / 100) * imgNatH}px`;

      el.innerHTML = `
        <div class="node-dot"></div>
        <div class="tooltip">
          <div class="tooltip-name">${loc.nome}</div>
          <div class="tooltip-type">${loc.tipo}</div>
        </div>
      `;

      el.addEventListener('click', (e) => {
        e.stopPropagation();
        if (onNodeClick) onNodeClick(loc);
      });

      inner.appendChild(el);
    });
  }

  function setNodeState(id, stateClass) {
    inner.querySelectorAll('.map-node').forEach(n => {
      if (parseInt(n.dataset.id) === id) {
        n.classList.remove('selected', 'origin', 'destination');
        if (stateClass) n.classList.add(stateClass);
      }
    });
  }

  function clearNodeStates() {
    inner.querySelectorAll('.map-node').forEach(n => {
      n.classList.remove('selected', 'origin', 'destination');
    });
  }

  function drawRoute(steps) {
    svgOverlay.innerHTML = '';
    if (!steps || steps.length < 2) return;

    // Animated gradient
    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = `
      <linearGradient id="routeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="0%" stop-color="#22c55e"/>
        <stop offset="100%" stop-color="#ef4444"/>
      </linearGradient>
      <filter id="glow"><feGaussianBlur stdDeviation="3" result="blur"/>
        <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
      </filter>
    `;
    svgOverlay.appendChild(defs);

    // Build path
    const points = steps.map(s => ({
      x: (s.x / 100) * imgNatW,
      y: (s.y / 100) * imgNatH
    }));

    const d = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');

    // Glow background
    const bgPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    bgPath.setAttribute('d', d);
    bgPath.setAttribute('fill', 'none');
    bgPath.setAttribute('stroke', 'rgba(99,102,241,0.3)');
    bgPath.setAttribute('stroke-width', '12');
    bgPath.setAttribute('stroke-linecap', 'round');
    bgPath.setAttribute('stroke-linejoin', 'round');
    svgOverlay.appendChild(bgPath);

    // Main line
    const mainPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    mainPath.setAttribute('d', d);
    mainPath.setAttribute('fill', 'none');
    mainPath.setAttribute('stroke', 'url(#routeGrad)');
    mainPath.setAttribute('stroke-width', '4');
    mainPath.setAttribute('stroke-linecap', 'round');
    mainPath.setAttribute('stroke-linejoin', 'round');
    mainPath.setAttribute('filter', 'url(#glow)');

    const totalLen = mainPath.getTotalLength ? 0 : 1000;
    svgOverlay.appendChild(mainPath);

    // Animate
    try {
      const len = mainPath.getTotalLength();
      mainPath.style.strokeDasharray = len;
      mainPath.style.strokeDashoffset = len;
      mainPath.style.transition = 'none';
      requestAnimationFrame(() => {
        mainPath.style.transition = 'stroke-dashoffset 1.5s ease-in-out';
        mainPath.style.strokeDashoffset = '0';
      });
    } catch (e) {}
  }

  function clearRoute() {
    if (svgOverlay) svgOverlay.innerHTML = '';
  }

  function zoomIn() { zoom(0.2); }
  function zoomOut() { zoom(-0.2); }
  function resetView() { fitToContainer(); }

  return { init, renderNodes, setNodeState, clearNodeStates, drawRoute, clearRoute, zoomIn, zoomOut, resetView };
})();
