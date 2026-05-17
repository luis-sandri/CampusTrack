UPDATE caminhos c
JOIN locais o ON c.origem_id = o.id
JOIN locais d ON c.destino_id = d.id
SET c.peso = ROUND(SQRT(POWER(o.x - d.x, 2) + POWER(o.y - d.y, 2)), 2);
