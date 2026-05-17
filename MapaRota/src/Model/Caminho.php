<?php

namespace CampusTrack\Model;

/**
 * Caminho Model (Path / Edge)
 * 
 * Represents a connection between two locations on the map.
 */
class Caminho
{
    public int $id;
    public int $origem_id;
    public int $destino_id;
    public float $peso;
    public string $tipo;
    public bool $bidirecional;
    public bool $acessivel;

    /**
     * Create a Caminho from a database row
     */
    public static function fromArray(array $data): self
    {
        $caminho = new self();
        $caminho->id            = (int) $data['id'];
        $caminho->origem_id     = (int) $data['origem_id'];
        $caminho->destino_id    = (int) $data['destino_id'];
        $caminho->peso          = (float) $data['peso'];
        $caminho->tipo          = $data['tipo'] ?? 'caminhada';
        $caminho->bidirecional  = (bool) ($data['bidirecional'] ?? true);
        $caminho->acessivel     = (bool) ($data['acessivel'] ?? true);
        return $caminho;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'origem_id'    => $this->origem_id,
            'destino_id'   => $this->destino_id,
            'peso'         => $this->peso,
            'tipo'         => $this->tipo,
            'bidirecional' => $this->bidirecional,
            'acessivel'    => $this->acessivel,
        ];
    }
}
