document.addEventListener("DOMContentLoaded", () => {
    valida_sessao();

    document.getElementById("form-instituicao").addEventListener("submit", function (event) {
        event.preventDefault();
        adicionar_instituicao(this);
    });
});

async function adicionar_instituicao(form) {
    const resposta = await CampusTrack.form.enviar(form, "../../php/instituicoes/adicionar.php");
    CampusTrack.resposta.alertar(resposta);

    if (resposta.status === "ok") {
        window.location.href = "gerenciar_instituicao.html";
    }
}
