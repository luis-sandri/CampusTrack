<?php

namespace CampusTrack\Model;

/**
 * Evento Model (Event)
 * 
 * Represents a campus event linked to a location.
 */
class Evento
{
    public int $id;
    public string $titulo;
    public ?string $descricao;
    public int $local_id;
    public string $data_inicio;
    public ?string $data_fim;
    public string $tipo;
    public ?int $criado_por;
    public bool $ativo;

    // Joined data
    public ?string $local_nome = null;

    /**
     * Create an Evento from a database row
     */
    public static function fromArray(array $data): self
    {
        $evento = new self();
        $evento->id          = (int) $data['id'];
        $evento->titulo      = $data['titulo'];
        $evento->descricao   = $data['descricao'] ?? null;
        $evento->local_id    = (int) $data['local_id'];
        $evento->data_inicio = $data['data_inicio'];
        $evento->data_fim    = $data['data_fim'] ?? null;
        $evento->tipo        = $data['tipo'] ?? 'outro';
        $evento->criado_por  = isset($data['criado_por']) ? (int) $data['criado_por'] : null;
        $evento->ativo       = (bool) ($data['ativo'] ?? true);
        $evento->local_nome  = $data['local_nome'] ?? null;
        return $evento;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'titulo'      => $this->titulo,
            'descricao'   => $this->descricao,
            'local_id'    => $this->local_id,
            'local_nome'  => $this->local_nome,
            'data_inicio' => $this->data_inicio,
            'data_fim'    => $this->data_fim,
            'tipo'        => $this->tipo,
            'ativo'       => $this->ativo,
        ];
    }
}
