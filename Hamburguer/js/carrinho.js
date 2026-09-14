const URL_CARRINHO = "../php/carrinho.php";
function formatarPreco(valor) {
    return Number(valor).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
}
async function requisicaoCarrinho(dados) {
    try {
        const resposta = await fetch(URL_CARRINHO, {
            method: "POST",
            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams(dados)
        });
        const texto = await resposta.text();
        console.log(
            "Resposta do servidor:",
            texto
        );
        if (!resposta.ok) {
            throw new Error(
                `Erro HTTP ${resposta.status}`
            );
        }
        try {
            return JSON.parse(texto);
        } catch (erro) {
            console.error(
                "========== ERRO DO PHP =========="
            );
            console.error(texto);
            console.error(
                "================================="
            );
            alert(
                "ERRO REAL DO PHP:\n\n" +
                texto
            );
            return {
                sucesso: false,
                mensagem:
                    "O servidor retornou uma resposta inválida."
            };
        }
    } catch (erro) {
        console.error(
            "Erro na requisição:",
            erro
        );
        return {
            sucesso: false,
            mensagem:
                "Erro ao conectar com o servidor."
        };
    }
}
async function adicionarAoCarrinho(card) {
    const produtoId =
        card.dataset.produtoId;
    if (!produtoId) {
        alert(
            "Produto não identificado."
        );
        console.error(
            "O card não possui data-produto-id:",
            card
        );
        return;
    }
    const resultado =
        await requisicaoCarrinho({
            acao: "adicionar",
            id_produto: produtoId
        });
    if (resultado.sucesso) {
        sessionStorage.removeItem(
            "pedido_finalizado"
        );
        sessionStorage.removeItem(
            "numero_pedido"
        );
        mostrarMensagem(
            `${resultado.nome || "Produto"} foi adicionado ao carrinho!`
        );
    } else {
        alert(
            resultado.mensagem ||
            "Erro ao adicionar produto."
        );
    }
}
function ativarBotoesAdicionar() {
    const botoes =
        document.querySelectorAll(".add");
    botoes.forEach(botao => {
        botao.addEventListener(
            "click",
            async function () {
                const card =
                    this.closest(
                        ".burger-item, " +
                        ".bebida-item, " +
                        ".acompanhamento-item, " +
                        ".combo-item, " +
                        ".promo-card, " +
                        ".card-destaque"
                    );
                if (!card) {
                    console.error(
                        "Não encontrei o card do produto."
                    );
                    return;
                }
                await adicionarAoCarrinho(card);
            }
        );
    });
}
async function mostrarCarrinho() {
    const lista =
        document.getElementById(
            "lista-carrinho"
        );
    if (!lista) {
        return;
    }
    try {
        const resposta =
            await fetch(
                `${URL_CARRINHO}?acao=listar`
            );
        if (!resposta.ok) {
            throw new Error(
                `Erro HTTP ${resposta.status}`
            );
        }
        const dados =
            await resposta.json();
        if (!dados.sucesso) {
            lista.innerHTML =
                "<p>Erro ao carregar o carrinho.</p>";
            return;
        }
        lista.innerHTML = "";
        if (
            !dados.carrinho ||
            dados.carrinho.length === 0
        ) {
            lista.innerHTML = `
                <p style="
                    font-size: 20px;
                    color: #481c11;
                    text-align: center;
                ">
                    Seu carrinho está vazio.
                </p>
            `;
            atualizarResumo(
                0,
                0,
                0
            );
            return;
        }
        dados.carrinho.forEach(
            (produto, indice) => {
                const subtotalProduto =
                    Number(produto.preco) *
                    Number(produto.quantidade);
                const card =
                    document.createElement("div");
                card.className =
                    indice % 2 === 0
                        ? "pedido-card pao"
                        : "pedido-card carne";
                card.innerHTML = `
                    <div class="info">
                        <img
                            src="../imagens/${produto.imagem}"
                            alt="${produto.nome}"
                        >
                        <div>
                            <p>
                                ${produto.nome}
                            </p>
                            <span>
                                ${formatarPreco(
                                    subtotalProduto
                                )}
                            </span>
                        </div>
                    </div>
                    <div class="controles">
                        <button
                            class="btn"
                            onclick="diminuirProduto(${indice})"
                        >
                            −
                        </button>
                        <span class="quantidade">
                            ${produto.quantidade}
                        </span>
                        <button
                            class="btn"
                            onclick="aumentarProduto(${indice})"
                        >
                            +
                        </button>
                        <button
                            class="btn-excluir"
                            onclick="excluirProduto(${indice})"
                            title="Excluir produto"
                        >
                            🗑
                        </button>
                    </div>
                `;
                lista.appendChild(card);
            }
        );
        atualizarResumo(
            dados.subtotal,
            dados.entrega,
            dados.total
        );
    } catch (erro) {
        console.error(
            "Erro ao carregar carrinho:",
            erro
        );
        lista.innerHTML = `
            <p>
                Erro ao conectar com o servidor.
            </p>
        `;
    }
}
async function aumentarProduto(indice) {
    const resultado =
        await requisicaoCarrinho({
            acao: "aumentar",
            indice: indice
        });
    if (resultado.sucesso) {
        await mostrarCarrinho();
    } else {
        alert(
            resultado.mensagem ||
            "Não foi possível aumentar a quantidade."
        );
    }
}
async function diminuirProduto(indice) {
    const resultado =
        await requisicaoCarrinho({
            acao: "diminuir",
            indice: indice
        });
    if (resultado.sucesso) {
        await mostrarCarrinho();
    } else {
        alert(
            resultado.mensagem ||
            "Não foi possível diminuir o produto."
        );
    }
}
async function excluirProduto(indice) {
    const confirmar =
        confirm(
            "Deseja excluir este produto do carrinho?"
        );
    if (!confirmar) {
        return;
    }
    const resultado =
        await requisicaoCarrinho({
            acao: "excluir",
            indice: indice
        });
    if (resultado.sucesso) {
        await mostrarCarrinho();
    } else {
        alert(
            resultado.mensagem ||
            "Não foi possível excluir o produto."
        );
    }
}
function atualizarResumo(
    subtotal,
    entrega,
    total
) {
    const elementoSubtotal =
        document.getElementById(
            "subtotal"
        );
    const elementoEntrega =
        document.getElementById(
            "entrega"
        );
    const elementoTotal =
        document.getElementById(
            "total"
        );
    if (elementoSubtotal) {
        elementoSubtotal.textContent =
            formatarPreco(subtotal);
    }
    if (elementoEntrega) {
        elementoEntrega.textContent =
            formatarPreco(entrega);
    }
    if (elementoTotal) {
        elementoTotal.textContent =
            formatarPreco(total);
    }
}
async function cancelarCarrinho() {
    try {
        const resposta =
            await fetch(
                `${URL_CARRINHO}?acao=listar`
            );
        if (!resposta.ok) {
            throw new Error(
                `Erro HTTP ${resposta.status}`
            );
        }
        const dados =
            await resposta.json();
        if (!dados.sucesso) {
            alert(
                dados.mensagem ||
                "Não foi possível acessar o carrinho."
            );
            return;
        }
        if (
            !dados.carrinho ||
            dados.carrinho.length === 0
        ) {
            window.location.href =
                "../html/tela-inicial.html";
            return;
        }
        const rascunho = {
            carrinho:
                dados.carrinho,
            subtotal:
                dados.subtotal,
            entrega:
                dados.entrega,
            total:
                dados.total,
            observacao:
                dados.observacao || "",
            data:
                new Date().toISOString()
        };
        sessionStorage.setItem(
            "rascunho_carrinho",
            JSON.stringify(rascunho)
        );
        const resultado =
            await requisicaoCarrinho({
                acao: "limpar"
            });
        if (!resultado.sucesso) {
            alert(
                resultado.mensagem ||
                "Não foi possível limpar o carrinho."
            );
            return;
        }
        sessionStorage.removeItem(
            "pedido_finalizado"
        );
        sessionStorage.removeItem(
            "numero_pedido"
        );
        window.location.href =
            "../html/tela-inicial.html";
    } catch (erro) {
        console.error(
            "Erro ao cancelar carrinho:",
            erro
        );
        alert(
            "Não foi possível cancelar o carrinho."
        );
    }
}
async function confirmarPedido() {
    const campoObservacao =
        document.getElementById(
            "observacao"
        );
    const observacao =
        campoObservacao
            ? campoObservacao.value
            : "";
    const resposta =
        await requisicaoCarrinho({
            acao: "observacao",
            observacao: observacao
        });
    if (!resposta.sucesso) {
        alert(
            "Não foi possível salvar a observação."
        );
        return;
    }
    window.location.href =
        "../html-carrinho/endereco.html";
}
function mostrarMensagem(mensagem) {
    const mensagemElemento =
        document.createElement("div");
    mensagemElemento.textContent =
        mensagem;
    mensagemElemento.style.position =
        "fixed";
    mensagemElemento.style.top =
        "100px";
    mensagemElemento.style.right =
        "30px";
    mensagemElemento.style.background =
        "#481c11";
    mensagemElemento.style.color =
        "#fff";
    mensagemElemento.style.padding =
        "15px 25px";
    mensagemElemento.style.borderRadius =
        "15px";
    mensagemElemento.style.zIndex =
        "9999";
    mensagemElemento.style.fontSize =
        "16px";
    document.body.appendChild(
        mensagemElemento
    );
    setTimeout(() => {
        mensagemElemento.remove();
    }, 2000);
}
document.addEventListener(
    "DOMContentLoaded",
    function () {
        ativarBotoesAdicionar();
        mostrarCarrinho();
        const botaoConfirmar =
            document.getElementById(
                "btn-confirmar"
            );
        if (botaoConfirmar) {
            botaoConfirmar.addEventListener(
                "click",
                confirmarPedido
            );
        }
        const botaoCancelar =
            document.getElementById(
                "btn-cancelar"
            );
        if (botaoCancelar) {
            botaoCancelar.addEventListener(
                "click",
                cancelarCarrinho
            );
        }
    }
);
