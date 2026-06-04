document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    document.getElementById("form-organizador").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_organizador(this);
    });
});

async function adicionar_organizador(form) {
    const resposta = await CampusTrack.form.enviar(form, "../../php/organizadores/adicionar.php");
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "organizadores.html";
        return;
    }

    if (resposta.mensagem === "Acesso negado.") {
        window.location.href = "login.html";
    }
}
