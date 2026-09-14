const URL_CARRINHO = "../php/carrinho.php";
const URL_FINALIZAR = "../php/finalizar_pedido.php";
let dadosCarrinho = null;
function formatarPreco(valor) {
    return Number(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}
async function carregarCarrinho() {
    try {
        const resposta = await fetch(
            `${URL_CARRINHO}?acao=listar`,
            {
                cache: "no-store"
            }
        );
        if (!resposta.ok) {
            throw new Error(
                `Erro HTTP: ${resposta.status}`
            );
        }
        const dados = await resposta.json();
        console.log(
            "Carrinho carregado:",
            dados
        );
        return dados;
    } catch (erro) {
        console.error(
            "Erro ao carregar carrinho:",
            erro
        );
        return null;
    }
}
async function finalizarPedido() {
    if (
        sessionStorage.getItem(
            "pedido_finalizado"
        ) === "true"
    ) {
        console.log(
            "Pedido já foi finalizado."
        );
        const numeroPedido =
            sessionStorage.getItem(
                "numero_pedido"
            );
        const elementoNumero =
            document.getElementById(
                "numero-pedido"
            );
        if (
            elementoNumero &&
            numeroPedido
        ) {
            elementoNumero.textContent =
                `#${numeroPedido}`;
        }
        return;
    }
    if (
        !dadosCarrinho ||
        !dadosCarrinho.carrinho ||
        dadosCarrinho.carrinho.length === 0
    ) {
        console.error(
            "Não é possível finalizar: carrinho vazio.",
            dadosCarrinho
        );
        alert(
            "O carrinho está vazio. Volte ao carrinho e adicione um produto."
        );
        return;
    }
    const dados = new FormData();
    const tipoEntrega =
        sessionStorage.getItem(
            "tipo_entrega"
        ) || "Retirada";
    dados.append(
        "tipo_entrega",
        tipoEntrega
    );
    const formaPagamento =
        sessionStorage.getItem(
            "forma_pagamento"
        ) || "";
    const troco =
        sessionStorage.getItem(
            "troco"
        ) || "0";
    dados.append(
        "forma_pagamento",
        formaPagamento
    );
    dados.append(
        "troco",
        troco
    );
    let observacao =
        sessionStorage.getItem(
            "observacao"
        ) || "";
    if (
        !observacao &&
        dadosCarrinho.observacao
    ) {
        observacao =
            dadosCarrinho.observacao;
    }
    dados.append(
        "observacao",
        observacao
    );
    const enderecoSalvo =
        sessionStorage.getItem(
            "endereco"
        );
    if (enderecoSalvo) {
        try {
            const endereco =
                JSON.parse(enderecoSalvo);
            dados.append(
                "cidade",
                endereco.cidade || ""
            );
            dados.append(
                "estado",
                endereco.estado || ""
            );
            dados.append(
                "endereco",
                endereco.endereco || ""
            );
            dados.append(
                "setor",
                endereco.setor || ""
            );
            dados.append(
                "informacao_adicional",
                endereco.informacao || ""
            );
        } catch (erro) {
            console.error(
                "Erro ao ler endereço:",
                erro
            );
        }
    }
    try {
        console.log(
            "Enviando pedido para o servidor..."
        );
        const resposta = await fetch(
            URL_FINALIZAR,
            {
                method: "POST",
                body: dados
            }
        );
        if (!resposta.ok) {
            throw new Error(
                `Erro HTTP: ${resposta.status}`
            );
        }
        const texto =
            await resposta.text();
        console.log(
            "Resposta do servidor:",
            texto
        );
        let resultado;
        try {
            resultado =
                JSON.parse(texto);
        } catch (erro) {
            console.error(
                "Resposta não é JSON:",
                texto
            );
            throw new Error(
                "O servidor retornou uma resposta inválida."
            );
        }
        if (!resultado.sucesso) {
            console.error(
                "Erro ao finalizar:",
                resultado
            );
            alert(
                resultado.mensagem ||
                "Não foi possível finalizar o pedido."
            );
            return;
        }
        const numeroPedido =
            resultado.id_pedido;
        sessionStorage.setItem(
            "numero_pedido",
            numeroPedido
        );
        sessionStorage.setItem(
            "pedido_finalizado",
            "true"
        );
        const elementoNumero =
            document.getElementById(
                "numero-pedido"
            );
        if (elementoNumero) {
            elementoNumero.textContent =
                `#${numeroPedido}`;
        }
        console.log(
            "Pedido salvo com sucesso!",
            resultado
        );
    } catch (erro) {
        console.error(
            "Erro ao finalizar pedido:",
            erro
        );
        alert(
            "Erro ao conectar com o servidor. Veja o Console (F12) para mais detalhes."
        );
    }
}
function mostrarProdutos() {
    const lista =
        document.getElementById(
            "lista-produtos"
        );
    if (!lista) {
        return;
    }
    if (
        !dadosCarrinho ||
        !dadosCarrinho.sucesso ||
        !dadosCarrinho.carrinho
    ) {
        lista.innerHTML =
            "<p>Não foi possível carregar os produtos.</p>";
        return;
    }
    if (
        dadosCarrinho.carrinho.length === 0
    ) {
        lista.innerHTML =
            "<p>Nenhum produto no pedido.</p>";
        return;
    }
    lista.innerHTML = "";
    dadosCarrinho.carrinho.forEach(
        produto => {
            const item =
                document.createElement(
                    "div"
                );
            item.className =
                "produto-confirmacao";
            const precoTotal =
                Number(produto.preco) *
                Number(produto.quantidade);
            item.innerHTML = `
                <div>
                    <span class="produto-nome">
                        ${produto.nome}
                    </span>
                    <span class="produto-quantidade">
                        x${produto.quantidade}
                    </span>
                </div>
                <span class="produto-preco">
                    ${formatarPreco(precoTotal)}
                </span>
            `;
            lista.appendChild(item);
        }
    );
}
function mostrarEntrega() {
    const tipoEntrega =
        sessionStorage.getItem(
            "tipo_entrega"
        );
    const enderecoSalvo =
        sessionStorage.getItem(
            "endereco"
        );
    const tipoElemento =
        document.getElementById(
            "tipo-entrega"
        );
    const enderecoElemento =
        document.getElementById(
            "endereco-pedido"
        );
    const enderecoContainer =
        document.getElementById(
            "endereco-container"
        );
    if (tipoElemento) {
        tipoElemento.textContent =
            tipoEntrega === "Entrega"
                ? "Entrega"
                : "Retirada no local";
    }
    if (
        tipoEntrega !== "Entrega"
    ) {
        if (enderecoContainer) {
            enderecoContainer.style.display =
                "none";
        }
        return;
    }
    if (
        enderecoSalvo &&
        enderecoElemento
    ) {
        try {
            const endereco =
                JSON.parse(
                    enderecoSalvo
                );
            let textoEndereco =
                endereco.endereco || "";
            if (endereco.setor) {
                textoEndereco +=
                    ` - ${endereco.setor}`;
            }
            if (
                endereco.cidade ||
                endereco.estado
            ) {
                textoEndereco +=
                    ` - ${endereco.cidade || ""}`;
                if (endereco.estado) {
                    textoEndereco +=
                        `/${endereco.estado}`;
                }
            }
            if (
                endereco.informacao
            ) {
                textoEndereco +=
                    ` - ${endereco.informacao}`;
            }
            enderecoElemento.textContent =
                textoEndereco;
        } catch (erro) {
            console.error(
                "Erro ao carregar endereço:",
                erro
            );
            enderecoElemento.textContent =
                "Endereço não encontrado.";
        }
    } else if (
        enderecoElemento
    ) {
        enderecoElemento.textContent =
            "Endereço não informado.";
    }
}
function mostrarPagamento() {
    const pagamento =
        sessionStorage.getItem(
            "forma_pagamento"
        );
    const troco =
        sessionStorage.getItem(
            "troco"
        );
    const pagamentoElemento =
        document.getElementById(
            "forma-pagamento"
        );
    const trocoElemento =
        document.getElementById(
            "troco-pedido"
        );
    const trocoContainer =
        document.getElementById(
            "troco-container"
        );
    if (pagamentoElemento) {
        pagamentoElemento.textContent =
            pagamento || "-";
    }
    if (
        pagamento === "Dinheiro" &&
        troco &&
        Number(troco) > 0
    ) {
        if (trocoElemento) {
            trocoElemento.textContent =
                formatarPreco(troco);
        }
        if (trocoContainer) {
            trocoContainer.style.display =
                "";
        }
    } else {
        if (trocoContainer) {
            trocoContainer.style.display =
                "none";
        }
    }
}
function mostrarObservacao() {
    const elemento =
        document.getElementById(
            "observacao-pedido"
        );
    if (!elemento) {
        return;
    }
    const observacao =
        sessionStorage.getItem(
            "observacao"
        ) ||
        dadosCarrinho?.observacao ||
        "";
    if (
        observacao.trim()
    ) {
        elemento.textContent =
            observacao;
    } else {
        elemento.textContent =
            "Nenhuma observação.";
    }
}
function mostrarTotais() {
    if (
        !dadosCarrinho ||
        !dadosCarrinho.sucesso
    ) {
        return;
    }
    const subtotal =
        Number(
            sessionStorage.getItem(
                "subtotal"
            )
        ) ||
        Number(
            dadosCarrinho.subtotal
        ) ||
        0;
    const entrega =
        Number(
            sessionStorage.getItem(
                "valor_entrega"
            )
        ) ||
        0;
    const total =
        Number(
            sessionStorage.getItem(
                "total"
            )
        ) ||
        subtotal + entrega;
    const elementoSubtotal =
        document.getElementById(
            "subtotal-pedido"
        );
    const elementoEntrega =
        document.getElementById(
            "entrega-pedido"
        );
    const elementoTotal =
        document.getElementById(
            "total-pedido"
        );
    if (elementoSubtotal) {
        elementoSubtotal.textContent =
            formatarPreco(
                subtotal
            );
    }
    if (elementoEntrega) {
        elementoEntrega.textContent =
            formatarPreco(
                entrega
            );
    }
    if (elementoTotal) {
        elementoTotal.textContent =
            formatarPreco(
                total
            );
    }
}
document.addEventListener(
    "DOMContentLoaded",
    async function () {
        console.log(
            "Abrindo página de confirmação..."
        );
        dadosCarrinho =
            await carregarCarrinho();
        mostrarProdutos();
        mostrarEntrega();
        mostrarPagamento();
        mostrarObservacao();
        mostrarTotais();
        await finalizarPedido();
    }
);
