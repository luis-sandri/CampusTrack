<?php
/**
 * Admin: Ajuste de Posições dos Pontos
 * Acesse: http://localhost/campustrack/admin_posicoes.php
 */
require_once __DIR__ . '/autoload.php';
use CampusTrack\Core\Database;

$db = Database::getInstance();

// Salvar posições via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['posicoes'])) {
    $posicoes = json_decode($_POST['posicoes'], true);
    $stmt = $db->prepare('UPDATE locais SET x = :x, y = :y WHERE id = :id');
    foreach ($posicoes as $p) {
        $stmt->execute(['x' => $p['x'], 'y' => $p['y'], 'id' => $p['id']]);
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'total' => count($posicoes)]);
    exit;
}

// Carregar todos os locais
$stmt = $db->query('SELECT id, nome, tipo, x, y, is_waypoint FROM locais ORDER BY is_waypoint ASC, nome ASC');
$locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
$locaisJson = json_encode($locais);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Admin — Ajuste de Posições</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: sans-serif; background: #111; color: #eee; height: 100vh; display: flex; flex-direction: column; }
  #toolbar {
    background: #1e1e1e;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #333;
    flex-shrink: 0;
  }
  #toolbar h1 { font-size: 16px; color: #fff; }
  #toolbar span { font-size: 13px; color: #aaa; }
  #btn-save {
    margin-left: auto;
    background: #22c55e;
    color: #000;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
  }
  #btn-save:hover { background: #16a34a; }
  #status { font-size: 13px; color: #facc15; }

  #workspace {
    flex: 1;
    display: flex;
    overflow: hidden;
  }

  #sidebar {
    width: 220px;
    background: #1a1a1a;
    border-right: 1px solid #333;
    overflow-y: auto;
    flex-shrink: 0;
    padding: 8px;
  }
  #sidebar h2 { font-size: 12px; color: #888; text-transform: uppercase; padding: 6px 4px; }
  .local-item {
    padding: 6px 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
  }
  .local-item:hover { background: #2a2a2a; }
  .local-item.active { background: #3b3b00; }
  .dot-preview {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  #map-area {
    flex: 1;
    position: relative;
    overflow: hidden;
    background: #000;
    cursor: crosshair;
  }
  #map-img {
    position: absolute;
    top: 0; left: 0;
    transform-origin: top left;
    user-select: none;
    pointer-events: none;
  }
  .node {
    position: absolute;
    width: 20px; height: 20px;
    border-radius: 50%;
    border: 2px solid #fff;
    transform: translate(-50%, -50%);
    cursor: grab;
    transition: transform 0.1s;
    z-index: 10;
  }
  .node:hover { transform: translate(-50%, -50%) scale(1.4); z-index: 20; }
  .node.dragging { cursor: grabbing; transform: translate(-50%, -50%) scale(1.5); z-index: 30; }
  .node.selected { box-shadow: 0 0 0 3px #facc15; z-index: 25; }
  .node-label {
    position: absolute;
    top: 12px; left: 12px;
    background: rgba(0,0,0,0.75);
    color: #fff;
    font-size: 10px;
    padding: 2px 5px;
    border-radius: 3px;
    white-space: nowrap;
    pointer-events: none;
  }
  #coords-display {
    position: absolute;
    bottom: 10px; right: 10px;
    background: rgba(0,0,0,0.7);
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    color: #aaa;
    pointer-events: none;
  }
  #hint {
    position: absolute;
    top: 10px; left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    color: #facc15;
    pointer-events: none;
  }
</style>
</head>
<body>

<div id="toolbar">
  <h1>🗺 Admin — Ajuste de Posições</h1>
  <span>Arraste os pontos para a posição correta no mapa</span>
  <span id="status"></span>
  <button id="btn-save">💾 Salvar tudo</button>
</div>

<div id="workspace">
  <div id="sidebar">
    <h2>Locais</h2>
    <div id="local-list"></div>
    <h2 style="margin-top:12px">Waypoints</h2>
    <div id="waypoint-list"></div>
  </div>

  <div id="map-area">
    <img id="map-img" src="mapa.png" draggable="false">
    <div id="coords-display">x: — y: —</div>
    <div id="hint">Scroll para zoom • Arraste o fundo para mover • Arraste os pontos para reposicionar</div>
  </div>
</div>

<script>
const locais = <?= $locaisJson ?>;

// ── Map pan/zoom ──────────────────────────────────────────────
const mapArea = document.getElementById('map-area');
const mapImg  = document.getElementById('map-img');
const coordsDisplay = document.getElementById('coords-display');

let scale = 1, panX = 0, panY = 0;
let imgW = 0, imgH = 0;

mapImg.onload = () => {
  imgW = mapImg.naturalWidth;
  imgH = mapImg.naturalHeight;
  fitMap();
  renderNodes();
};
if (mapImg.complete && mapImg.naturalWidth) {
  imgW = mapImg.naturalWidth;
  imgH = mapImg.naturalHeight;
  fitMap();
  renderNodes();
}

function fitMap() {
  const cw = mapArea.clientWidth, ch = mapArea.clientHeight;
  scale = Math.min(cw / imgW, ch / imgH) * 0.95;
  panX = (cw - imgW * scale) / 2;
  panY = (ch - imgH * scale) / 2;
  applyTransform();
}

function applyTransform() {
  mapImg.style.transform = `translate(${panX}px,${panY}px) scale(${scale})`;
  // reposiciona todos os nós
  document.querySelectorAll('.node').forEach(el => {
    const id = parseInt(el.dataset.id);
    const loc = locais.find(l => l.id === id);
    if (loc) positionNode(el, loc.x, loc.y);
  });
}

// pan
let isPanning = false, panStart = {x:0,y:0}, panOrigin = {x:0,y:0};
mapArea.addEventListener('mousedown', e => {
  if (e.target.classList.contains('node')) return;
  isPanning = true;
  panStart = {x: e.clientX, y: e.clientY};
  panOrigin = {x: panX, y: panY};
  mapArea.style.cursor = 'grabbing';
});
window.addEventListener('mousemove', e => {
  if (!isPanning) return;
  panX = panOrigin.x + (e.clientX - panStart.x);
  panY = panOrigin.y + (e.clientY - panStart.y);
  applyTransform();

  // show coords under cursor
  const rect = mapArea.getBoundingClientRect();
  const mx = (e.clientX - rect.left - panX) / scale;
  const my = (e.clientY - rect.top  - panY) / scale;
  coordsDisplay.textContent = `x: ${(mx/imgW*100).toFixed(2)}%  y: ${(my/imgH*100).toFixed(2)}%`;
});
window.addEventListener('mouseup', () => {
  isPanning = false;
  mapArea.style.cursor = 'crosshair';
});

mapArea.addEventListener('wheel', e => {
  e.preventDefault();
  const delta = e.deltaY > 0 ? -0.1 : 0.1;
  const rect = mapArea.getBoundingClientRect();
  const mx = e.clientX - rect.left;
  const my = e.clientY - rect.top;
  const oldScale = scale;
  scale = Math.max(0.2, Math.min(5, scale + delta));
  panX = mx - (mx - panX) * (scale / oldScale);
  panY = my - (my - panY) * (scale / oldScale);
  applyTransform();
}, { passive: false });

// ── Nodes ─────────────────────────────────────────────────────
const COLORS = {
  sala: '#3b82f6', biblioteca: '#8b5cf6', laboratorio: '#06b6d4',
  auditorio: '#6366f1', portao: '#f59e0b', corredor: '#6b7280',
  quadra: '#22c55e', ginasio: '#10b981', arena: '#dc2626', outro: '#ec4899',
};

function positionNode(el, xPct, yPct) {
  const px = panX + (xPct / 100) * imgW * scale;
  const py = panY + (yPct / 100) * imgH * scale;
  el.style.left = px + 'px';
  el.style.top  = py + 'px';
}

let selectedId = null;

function renderNodes() {
  document.querySelectorAll('.node').forEach(n => n.remove());
  const localList    = document.getElementById('local-list');
  const waypointList = document.getElementById('waypoint-list');
  localList.innerHTML = '';
  waypointList.innerHTML = '';

  locais.forEach(loc => {
    // sidebar item
    const item = document.createElement('div');
    item.className = 'local-item';
    item.dataset.id = loc.id;
    const dot = document.createElement('div');
    dot.className = 'dot-preview';
    dot.style.background = COLORS[loc.tipo] || '#aaa';
    item.appendChild(dot);
    item.appendChild(document.createTextNode(loc.nome));
    item.addEventListener('click', () => selectNode(loc.id));
    (loc.is_waypoint == 1 ? waypointList : localList).appendChild(item);

    // map node
    const el = document.createElement('div');
    el.className = 'node';
    el.dataset.id = loc.id;
    el.style.background = COLORS[loc.tipo] || '#aaa';
    el.title = loc.nome;

    const label = document.createElement('div');
    label.className = 'node-label';
    label.textContent = loc.nome;
    el.appendChild(label);

    positionNode(el, loc.x, loc.y);
    makeDraggable(el, loc);
    mapArea.appendChild(el);
  });
}

function selectNode(id) {
  selectedId = id;
  document.querySelectorAll('.node').forEach(n => n.classList.remove('selected'));
  document.querySelectorAll('.local-item').forEach(n => n.classList.remove('active'));
  const node = document.querySelector(`.node[data-id="${id}"]`);
  const item = document.querySelector(`.local-item[data-id="${id}"]`);
  if (node) { node.classList.add('selected'); node.scrollIntoView?.({block:'nearest'}); }
  if (item) { item.classList.add('active'); item.scrollIntoView({block:'nearest'}); }
}

function makeDraggable(el, loc) {
  let dragging = false, startMouse = {}, startLoc = {};

  el.addEventListener('mousedown', e => {
    e.stopPropagation();
    dragging = true;
    el.classList.add('dragging');
    selectNode(loc.id);
    startMouse = { x: e.clientX, y: e.clientY };
    startLoc   = { x: loc.x, y: loc.y };
  });

  window.addEventListener('mousemove', e => {
    if (!dragging) return;
    const dx = (e.clientX - startMouse.x) / scale;
    const dy = (e.clientY - startMouse.y) / scale;
    loc.x = Math.max(0, Math.min(100, startLoc.x + (dx / imgW * 100)));
    loc.y = Math.max(0, Math.min(100, startLoc.y + (dy / imgH * 100)));
    positionNode(el, loc.x, loc.y);
    coordsDisplay.textContent = `${loc.nome}  x: ${loc.x.toFixed(2)}%  y: ${loc.y.toFixed(2)}%`;
    document.getElementById('status').textContent = '● não salvo';
  });

  window.addEventListener('mouseup', () => {
    if (dragging) {
      dragging = false;
      el.classList.remove('dragging');
    }
  });
}

// ── Save ──────────────────────────────────────────────────────
document.getElementById('btn-save').addEventListener('click', async () => {
  const posicoes = locais.map(l => ({ id: l.id, x: parseFloat(l.x.toFixed(4)), y: parseFloat(l.y.toFixed(4)) }));
  const form = new FormData();
  form.append('posicoes', JSON.stringify(posicoes));

  try {
    const res = await fetch('admin_posicoes.php', { method: 'POST', body: form });
    const data = await res.json();
    if (data.ok) {
      document.getElementById('status').textContent = `✔ ${data.total} pontos salvos!`;
      setTimeout(() => document.getElementById('status').textContent = '', 3000);
    }
  } catch(e) {
    document.getElementById('status').textContent = '✖ Erro ao salvar';
  }
});
</script>
</body>
</html>
