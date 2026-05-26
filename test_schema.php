<?php
$conexao = new mysqli("localhost", "root", "", "campustrack");
$res = $conexao->query("DESCRIBE Locais");
$data = [];
while($row = $res->fetch_assoc()) { $data[] = $row; }
print_r($data);
