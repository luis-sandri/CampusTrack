<?php
$conexao = new mysqli("localhost", "root", "", "campustrack");
$res = $conexao->query("SELECT * FROM Evento ORDER BY id_evento DESC LIMIT 5");
$data = [];
while($row = $res->fetch_assoc()) { $data[] = $row; }
print_r($data);
