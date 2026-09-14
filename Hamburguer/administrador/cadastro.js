
document.addEventListener("DOMContentLoaded", function () {

    carregarCategorias();
    carregarProdutos();

    document
        .getElementById("formProduto")
        .addEventListener("submit", cadastrarProduto);

    document
        .getElementById("btnLimpar")
        .addEventListener("click", limparFormulario);

    document
        .getElementById("imagem")
        .addEventListener("change", mostrarNomeImagem);

});

function carregarCategorias() {

    const select = document.getElementById("categoria");

    if (!select) {
        console.error("Elemento #categoria não encontrado.");
        return;
    }

    select.innerHTML =
        '<option value="">Carregando categorias...</option>';

    fetch("../Crud/produtos/categorias.php")

        .then(function (resposta) {

            console.log("Status categorias:", resposta.status);

            if (!resposta.ok) {
                throw new Error(
                    "Erro HTTP " + resposta.status
                );
            }

            return resposta.json();
        })

        .then(function (dados) {

            console.log("Categorias recebidas:", dados);

            if (!dados.sucesso) {
                throw new Error(
                    dados.mensagem || "Erro ao carregar categorias."
                );
            }

            select.innerHTML =
                '<option value="">Selecione a categoria</option>';

            if (
                !dados.categorias ||
                dados.categorias.length === 0
            ) {

                select.innerHTML =
                    '<option value="">Nenhuma categoria cadastrada</option>';

                return;
            }

            dados.categorias.forEach(function (categoria) {

                const option =
                    document.createElement("option");

                option.value =
                    categoria.id_categoria;

                option.textContent =
                    categoria.nome;

                select.appendChild(option);

            });

        })

        .catch(function (erro) {

            console.error(
                "ERRO AO CARREGAR CATEGORIAS:",
                erro
            );

            select.innerHTML =
                '<option value="">Erro ao carregar categorias</option>';

        });
}


function cadastrarProduto(event) {

    event.preventDefault();

    const formulario =
        document.getElementById("formProduto");

    const dados =
        new FormData(formulario);

    const idEditando =
        formulario.dataset.editando;

    let endereco;


    if (idEditando) {

        endereco =
            "../Crud/produtos/editar.php";

        dados.append(
            "id_produto",
            idEditando
        );

    } else {

        endereco =
            "../Crud/produtos/cadastrar.php";

    }


    fetch(endereco, {

        method: "POST",
        body: dados

    })

        .then(function (resposta) {

            if (!resposta.ok) {
                throw new Error(
                    "Erro HTTP: " + resposta.status
                );
            }

            return resposta.json();

        })

        .then(function (dados) {

            if (!dados.sucesso) {

                alert(dados.mensagem);
                return;

            }

            alert(dados.mensagem);

            limparFormulario();

            carregarProdutos();

        })

        .catch(function (erro) {

            console.error(erro);

            alert("Erro ao salvar o produto.");

        });

}

function carregarProdutos() {

    const lista =
        document.getElementById("listaProdutos");

    fetch("../Crud/produtos/listar.php")

        .then(function (resposta) {

            if (!resposta.ok) {
                throw new Error(
                    "Erro HTTP: " + resposta.status
                );
            }

            return resposta.json();

        })

        .then(function (dados) {

            if (!dados.sucesso) {
                throw new Error(dados.mensagem);
            }

            lista.innerHTML = "";


            if (
                !dados.produtos ||
                dados.produtos.length === 0
            ) {

                lista.innerHTML =
                    "<p>Nenhum produto cadastrado.</p>";

                return;

            }


            dados.produtos.forEach(function (produto) {

                const item =
                    document.createElement("div");

                item.className =
                    "produto";


                const preco =
                    Number(produto.preco)
                        .toFixed(2)
                        .replace(".", ",");

                let imagemHTML = "";


                if (produto.imagem) {

                    imagemHTML = `
                        <img
                            src="../../imagens/${produto.imagem}"
                            alt="${produto.nome}"
                            class="imagem-produto"
                        >
                    `;

                } else {

                    imagemHTML = `
                        <div class="sem-imagem">
                            Sem imagem
                        </div>
                    `;

                }

                item.innerHTML = `

                    <div class="produto-imagem">

                        ${imagemHTML}

                    </div>


                    <span>
                        ${produto.nome}
                    </span>


                    <span>
                        ${produto.categoria}
                    </span>


                    <span>
                        R$ ${preco}
                    </span>


                    <span>
                        ${produto.status}
                    </span>


                    <div class="acoes">

                        <button
                            type="button"
                            class="editar"
                            onclick="editarProduto(${produto.id_produto})">

                            Editar

                        </button>


                        <button
                            type="button"
                            class="excluir"
                            onclick="excluirProduto(${produto.id_produto})">

                            Excluir

                        </button>

                    </div>

                `;


                lista.appendChild(item);

            });

        })

        .catch(function (erro) {

            console.error(
                "Erro ao carregar produtos:",
                erro
            );

        });

}

function editarProduto(id) {

    fetch(
        "../Crud/produtos/editar.php?id=" + id
    )

        .then(function (resposta) {
            return resposta.json();
        })

        .then(function (dados) {

            if (!dados.sucesso) {

                alert(dados.mensagem);
                return;

            }


            const produto =
                dados.produto;


            document.getElementById("nome").value =
                produto.nome;


            document.getElementById("descricao").value =
                produto.descricao;


            document.getElementById("preco").value =
                produto.preco;


            document.getElementById("categoria").value =
                produto.id_categoria;


            document.getElementById("tempo_preparo").value =
                produto.tempo_preparo;


            const status =
                document.querySelector(
                    'input[name="status"][value="' +
                    produto.status +
                    '"]'
                );


            if (status) {
                status.checked = true;
            }


            document.getElementById("promocao").checked =
                produto.promocao === "Sim";


            document.getElementById("disponivel_entrega").checked =
                produto.disponivel_entrega === "Sim";


            const formulario =
                document.getElementById("formProduto");


            formulario.dataset.editando =
                id;


            document.getElementById("btnSalvar").textContent =
                "Atualizar Produto";


            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        })

        .catch(function (erro) {

            console.error(erro);

            alert("Erro ao buscar o produto.");

        });

}

function excluirProduto(id) {

    const confirmar =
        confirm(
            "Tem certeza que deseja excluir este produto?"
        );


    if (!confirmar) {
        return;
    }


    const dados =
        new FormData();

    dados.append(
        "id_produto",
        id
    );


    fetch(
        "../Crud/produtos/excluir.php",
        {
            method: "POST",
            body: dados
        }
    )

        .then(function (resposta) {
            return resposta.json();
        })

        .then(function (dados) {

            if (!dados.sucesso) {

                alert(dados.mensagem);
                return;

            }

            alert(dados.mensagem);

            carregarProdutos();

        })

        .catch(function (erro) {

            console.error(erro);

            alert("Erro ao excluir o produto.");

        });

}

function limparFormulario() {

    const formulario =
        document.getElementById("formProduto");


    formulario.reset();


    formulario.dataset.editando =
        "";


    document.getElementById("categoria").value =
        "";


    document.getElementById("nome-arquivo").textContent =
        "Nenhum arquivo escolhido";


    document.getElementById("btnSalvar").textContent =
        "Salvar Produto";

}

function mostrarNomeImagem() {

    const imagem =
        document.getElementById("imagem");

    const nomeArquivo =
        document.getElementById("nome-arquivo");


    if (imagem.files.length > 0) {

        nomeArquivo.textContent =
            imagem.files[0].name;

    } else {

        nomeArquivo.textContent =
            "Nenhum arquivo escolhido";

    }

}

