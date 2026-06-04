<?php
include_once __DIR__ . "/sessao.php";

encerrar_sessao();

responder_json(resposta_ok("Sessao encerrada."));
