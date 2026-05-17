<?php

namespace CampusTrack\Model;

/**
 * Local Model (Location / Map Node)
 * 
 * Represents a navigable point on the campus map.
 */
class Local
{
    public int $id;
    public string $nome;
    public ?string $descricao;
    public string $tipo;
    public float $x;
    public float $y;
    public ?int $andar_id;
    public ?int $edificio_id;
    public bool $is_navigable;
    public bool $is_waypoint;
    public string $icone;

    /**
     * Create a Local from a database row
     */
    public static function fromArray(array $data): self
    {
        $local = new self();
        $local->id           = (int) $data['id'];
        $local->nome         = $data['nome'];
        $local->descricao    = $data['descricao'] ?? null;
        $local->tipo         = $data['tipo'];
        $local->x            = (float) $data['x'];
        $local->y            = (float) $data['y'];
        $local->andar_id     = isset($data['andar_id']) ? (int) $data['andar_id'] : null;
        $local->edificio_id  = isset($data['edificio_id']) ? (int) $data['edificio_id'] : null;
        $local->is_navigable = (bool) ($data['is_navigable'] ?? true);
        $local->is_waypoint  = (bool) ($data['is_waypoint'] ?? false);
        $local->icone        = $data['icone'] ?? 'pin';
        return $local;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'nome'         => $this->nome,
            'descricao'    => $this->descricao,
            'tipo'         => $this->tipo,
            'x'            => $this->x,
            'y'            => $this->y,
            'andar_id'     => $this->andar_id,
            'edificio_id'  => $this->edificio_id,
            'is_navigable' => $this->is_navigable,
            'is_waypoint'  => $this->is_waypoint,
            'icone'        => $this->icone,
        ];
    }
}
