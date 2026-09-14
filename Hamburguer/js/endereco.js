document.addEventListener("DOMContentLoaded", function () {
    const retirada = document.querySelector(
        'input[name="entrega"][value="retirada"]'
    );
    const entrega = document.querySelector(
        'input[name="entrega"][value="entrega"]'
    );
    const camposEndereco = document.querySelectorAll(
        ".form-endereco input, .form-endereco textarea"
    );
    const botaoConfirmar =
        document.querySelector(".btn-confirmar");
    const mensagem =
        document.createElement("div");
    mensagem.className = "mensagem-endereco";
    document.querySelector(".entrega-container")
        .appendChild(mensagem);
    function atualizarCampos() {
        const estaEntregando = entrega.checked;
        camposEndereco.forEach(campo => {
            campo.disabled = !estaEntregando;
            if (estaEntregando) {
                campo.style.opacity = "1";
            } else {
                campo.style.opacity = "0.5";
            }
        });
    }
    function mostrarMensagem(texto) {
        mensagem.textContent = texto;
        mensagem.style.display = "block";
        setTimeout(() => {
            mensagem.style.display = "none";
        }, 2500);
    }
    function validarEndereco() {
        const cidade =
            document.querySelector("#cidade").value.trim();
        const estado =
            document.querySelector("#estado").value.trim();
        const endereco =
            document.querySelector("#endereco").value.trim();
        const setor =
            document.querySelector("#setor").value.trim();
        if (!cidade || !estado || !endereco || !setor) {
            mostrarMensagem(
                "Preencha os campos obrigatórios do endereço."
            );
            return false;
        }
        return true;
    }
    retirada.addEventListener("change", function () {
        atualizarCampos();
    });
    entrega.addEventListener("change", function () {
        atualizarCampos();
    });
    botaoConfirmar.addEventListener("click", function () {
        /*
         * RETIRADA
         */
        if (retirada.checked) {
            sessionStorage.setItem(
                "tipo_entrega",
                "Retirada"
            );
            sessionStorage.setItem(
                "valor_entrega",
                "0"
            );
            window.location.href =
                "confirmacao.html";
            return;
        }
        /*
         * ENTREGA
         */
        if (entrega.checked) {
            if (!validarEndereco()) {
                return;
            }
            const dadosEndereco = {
                cidade:
                    document.querySelector("#cidade").value.trim(),
                estado:
                    document.querySelector("#estado").value.trim(),
                endereco:
                    document.querySelector("#endereco").value.trim(),
                setor:
                    document.querySelector("#setor").value.trim(),
                informacao:
                    document.querySelector("#informacao").value.trim()
            };
            sessionStorage.setItem(
                "tipo_entrega",
                "Entrega"
            );
            sessionStorage.setItem(
                "valor_entrega",
                "5"
            );
            sessionStorage.setItem(
                "endereco",
                JSON.stringify(dadosEndereco)
            );
            window.location.href =
                "forma-pagamento.html";
        }
    });
    /*
     * Começa com ENTREGA selecionada.
     */
    atualizarCampos();
});