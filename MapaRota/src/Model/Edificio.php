<?php

namespace CampusTrack\Model;

/**
 * Edificio Model (Building)
 */
class Edificio
{
    public int $id;
    public string $nome;
    public string $codigo;
    public ?string $descricao;
    public string $cor;

    /**
     * Create an Edificio from a database row
     */
    public static function fromArray(array $data): self
    {
        $edificio = new self();
        $edificio->id        = (int) $data['id'];
        $edificio->nome      = $data['nome'];
        $edificio->codigo    = $data['codigo'];
        $edificio->descricao = $data['descricao'] ?? null;
        $edificio->cor       = $data['cor'] ?? '#3B82F6';
        return $edificio;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'nome'      => $this->nome,
            'codigo'    => $this->codigo,
            'descricao' => $this->descricao,
            'cor'       => $this->cor,
        ];
    }
}
