(function () {
    var overlay = null;

    function criarAlerta() {
        if (overlay) {
            return overlay;
        }

        overlay = document.createElement("div");
        overlay.className = "alerta-overlay hidden";
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        overlay.innerHTML =
            '<div class="alerta-modal">' +
                '<p class="alerta-titulo"></p>' +
                '<p class="alerta-texto"></p>' +
                '<button type="button" class="alerta-btn">OK</button>' +
            "</div>";

        document.body.appendChild(overlay);

        overlay.querySelector(".alerta-btn").addEventListener("click", fecharAlerta);
        overlay.addEventListener("click", function (evento) {
            if (evento.target === overlay) {
                fecharAlerta();
            }
        });

        return overlay;
    }

    function fecharAlerta() {
        if (!overlay) {
            return;
        }
        overlay.classList.add("hidden");
    }

    function mostrarAlerta(tipo, mensagem) {
        if (!mensagem) {
            return;
        }

        var caixa = criarAlerta();
        var titulo = caixa.querySelector(".alerta-titulo");
        var texto = caixa.querySelector(".alerta-texto");
        var modal = caixa.querySelector(".alerta-modal");
        var sucesso = tipo === "sucesso";

        titulo.textContent = sucesso ? "SUCESSO" : "ATENÇÃO";
        texto.textContent = mensagem;
        modal.classList.toggle("alerta-modal-sucesso", sucesso);
        modal.classList.toggle("alerta-modal-erro", !sucesso);
        caixa.classList.remove("hidden");
        caixa.querySelector(".alerta-btn").focus();
    }

    function limparUrl() {
        if (!window.history || !window.history.replaceState) {
            return;
        }

        var url = new URL(window.location.href);
        if (!url.searchParams.has("alerta") && !url.searchParams.has("msg")) {
            return;
        }

        url.searchParams.delete("alerta");
        url.searchParams.delete("msg");
        var limpa = url.pathname + (url.search ? url.search : "") + url.hash;
        window.history.replaceState({}, document.title, limpa);
    }

    document.addEventListener("DOMContentLoaded", function () {
        var params = new URLSearchParams(window.location.search);
        var tipo = params.get("alerta");
        var mensagem = params.get("msg");

        if (tipo && mensagem) {
            mostrarAlerta(tipo, mensagem);
            limparUrl();
        }
    });

    document.addEventListener("keydown", function (evento) {
        if (evento.key === "Escape") {
            fecharAlerta();
        }
    });

    window.mostrarAlerta = mostrarAlerta;
})();
