/**
 * CampusTrack API Module
 * Usa endpoints .php diretos (sem mod_rewrite)
 */
const API = (() => {
  const BASE_URL = "/CampusTrack/MapaRota/api";

  async function request(endpoint, options = {}) {
    // Converte /mapa → /mapa.php, etc.
    const phpEndpoint = resolveEndpoint(endpoint);
    const url = `${BASE_URL}${phpEndpoint}`;

    const config = {
      headers: { 'Content-Type': 'application/json' },
      ...options,
    };

    try {
      const res = await fetch(url, config);
      const text = await res.text();

      // Detecta HTML (geralmente erro 404/500 do PHP)
      if (text.trim().startsWith('<')) {
        throw new Error(`404: endpoint não encontrado — ${url}`);
      }

      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        throw new Error(`Resposta inválida: ${text.slice(0, 100)}`);
      }

      if (!res.ok) {
        throw new Error(data.message || data.error || 'Erro na requisição');
      }

      return data.data !== undefined ? data.data : data;

    } catch (err) {
      console.error(`API Error [${endpoint}]:`, err);
      throw err;
    }
  }

  /**
   * Mapeia endpoint lógico → arquivo .php + query string
   */
  function resolveEndpoint(endpoint) {
    const [pathPart, queryPart] = endpoint.split('?');
    const segments = pathPart.replace(/^\//, '').split('/');

    const base = segments[0];        // ex: "locais"
    const sub  = segments[1] || '';  // ex: "search", "42", "proximos"

    let file = `/${base}.php`;
    const params = new URLSearchParams(queryPart || '');

    if (sub) {
      if (/^\d+$/.test(sub)) {
        params.set('id', sub);
      } else {
        params.set('action', sub);
      }
    }

    const qs = params.toString();
    return qs ? `${file}?${qs}` : file;
  }

  return {
    getLocais:      (tipo) => request(tipo ? `/locais?tipo=${tipo}` : '/locais'),
    getLocal:       (id)   => request(`/locais/${id}`),
    searchLocais:   (q)    => request(`/locais/search?q=${encodeURIComponent(q)}`),
    getCaminhos:    ()     => request('/caminhos'),
    getGrafo:       ()     => request('/caminhos/grafo'),
    getMapData:     ()     => request('/mapa'),

    calcularRota: (origemId, destinoId, acessivel = false) =>
      request('/rota', {
        method: 'POST',
        body: JSON.stringify({
          origem_id: origemId,
          destino_id: destinoId,
          acessivel
        }),
      }),

    getEventos:     ()            => request('/eventos'),
    getProxEventos: (limit = 10)  => request(`/eventos/proximos?limit=${limit}`),
    getEvento:      (id)          => request(`/eventos/${id}`),
  };
})();