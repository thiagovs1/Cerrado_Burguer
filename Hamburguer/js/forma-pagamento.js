const URL_CARRINHO = "../php/carrinho.php";
function formatarPreco(valor) {
    return Number(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}
async function carregarCarrinho() {
    try {
        const resposta = await fetch(
            `${URL_CARRINHO}?acao=listar`
        );
        return await resposta.json();
    } catch (erro) {
        console.error(
            "Erro ao carregar carrinho:",
            erro
        );
        return null;
    }
}
function carregarEndereco() {
    const tipoEntrega =
        sessionStorage.getItem("tipo_entrega");
    const enderecoSalvo =
        sessionStorage.getItem("endereco");
    if (tipoEntrega === "Retirada") {
        document.getElementById(
            "endereco-resumo"
        ).textContent =
            "Retirada no local";
        document.getElementById(
            "cidade-resumo"
        ).textContent =
            "Cerrado Burguer";
        return;
    }
    if (enderecoSalvo) {
        const endereco =
            JSON.parse(enderecoSalvo);
        document.getElementById(
            "endereco-resumo"
        ).textContent =
            `${endereco.endereco}, ${endereco.setor}`;
        document.getElementById(
            "cidade-resumo"
        ).textContent =
            `${endereco.cidade} - ${endereco.estado}`;
    }
}
async function carregarResumo() {
    const dados =
        await carregarCarrinho();
    if (!dados || !dados.sucesso) {
        return;
    }
    const tipoEntrega =
        sessionStorage.getItem("tipo_entrega");
    let entrega = 0;
    if (tipoEntrega === "Entrega") {
        entrega = 5;
    }
    const subtotal =
        Number(dados.subtotal);
    const total =
        subtotal + entrega;
    document.getElementById(
        "subtotal-pagamento"
    ).textContent =
        formatarPreco(subtotal);
    document.getElementById(
        "entrega-pagamento"
    ).textContent =
        formatarPreco(entrega);
    document.getElementById(
        "total-pagamento"
    ).textContent =
        formatarPreco(total);
    sessionStorage.setItem(
        "subtotal",
        subtotal
    );
    sessionStorage.setItem(
        "valor_entrega",
        entrega
    );
    sessionStorage.setItem(
        "total",
        total
    );
}
function configurarTroco() {
    const trocoSim =
        document.getElementById("troco-sim");
    const valorTroco =
        document.getElementById("valor-troco");
    trocoSim.addEventListener(
        "change",
        function () {
            valorTroco.disabled = false;
            valorTroco.focus();
        }
    );
    const pagamentos =
        document.querySelectorAll(
            'input[name="pagamento"]'
        );
    pagamentos.forEach(pagamento => {
        pagamento.addEventListener(
            "change",
            function () {
                if (
                    this.value !== "Dinheiro"
                ) {
                    trocoSim.checked = false;
                    valorTroco.value = "";
                    valorTroco.disabled = true;
                }
            }
        );
    });
}
function confirmarPagamento() {
    const pagamentoSelecionado =
        document.querySelector(
            'input[name="pagamento"]:checked'
        );
    if (!pagamentoSelecionado) {
        mostrarMensagem(
            "Selecione uma forma de pagamento."
        );
        return;
    }
    const pagamento =
        pagamentoSelecionado.value;
    let troco = "";
    if (pagamento === "Dinheiro") {
        const trocoSim =
            document.getElementById(
                "troco-sim"
            );
        const valorTroco =
            document.getElementById(
                "valor-troco"
            );
        if (trocoSim.checked) {
            troco =
                valorTroco.value.trim();
            if (!troco) {
                mostrarMensagem(
                    "Informe o valor para o troco."
                );
                valorTroco.focus();
                return;
            }
        }
    }
    sessionStorage.setItem(
        "forma_pagamento",
        pagamento
    );
    sessionStorage.setItem(
        "troco",
        troco
    );
    window.location.href =
        "confirmacao.html";
}
function mostrarMensagem(texto) {
    let mensagem =
        document.querySelector(
            ".mensagem-pagamento"
        );
    if (!mensagem) {
        mensagem =
            document.createElement("div");
        mensagem.className =
            "mensagem-pagamento";
        document.body.appendChild(
            mensagem
        );
    }
    mensagem.textContent = texto;
    mensagem.style.display =
        "block";
    setTimeout(() => {
        mensagem.style.display =
            "none";
    }, 2500);
}
document.addEventListener(
    "DOMContentLoaded",
    async function () {
        carregarEndereco();
        await carregarResumo();
        configurarTroco();
        document
            .getElementById(
                "btn-confirmar-pagamento"
            )
            .addEventListener(
                "click",
                confirmarPagamento
            );
    }
);