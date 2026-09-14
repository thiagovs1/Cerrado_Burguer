const API_CATEGORIAS = "../Crud/categorias/";

const tabela = document.querySelector("#tabelaCategorias");
const busca = document.querySelector("#buscaCategoria");
const filtroStatus = document.querySelector("#filtroStatus");

const modal = document.querySelector("#modalCategoria");
const form = document.querySelector("#formCategoria");

const tituloModal = document.querySelector("#tituloModal");

const idCategoria = document.querySelector("#idCategoria");
const nomeCategoria = document.querySelector("#nomeCategoria");
const descricaoCategoria = document.querySelector("#descricaoCategoria");
const statusCategoria = document.querySelector("#statusCategoria");

// =====================================================
// CARREGAR CATEGORIAS
// =====================================================

async function carregarCategorias() {


const params = new URLSearchParams();

if (busca.value.trim() !== "") {
    params.append("busca", busca.value.trim());
}

if (filtroStatus.value !== "") {
    params.append("status", filtroStatus.value);
}

try {

    const resposta = await fetch(
        API_CATEGORIAS + "listar.php?" + params.toString()
    );

    if (!resposta.ok) {
        throw new Error("Erro HTTP: " + resposta.status);
    }

    const dados = await resposta.json();

    if (!dados.sucesso) {
        alert(
            dados.mensagem ||
            "Erro ao carregar categorias."
        );
        return;
    }

    tabela.innerHTML = "";


    // =================================================
    // NENHUMA CATEGORIA
    // =================================================

    if (
        !dados.categorias ||
        dados.categorias.length === 0
    ) {

        tabela.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center;">
                    Nenhuma categoria encontrada.
                </td>
            </tr>
        `;

        atualizarCards([]);

        return;
    }


    // =================================================
    // MOSTRAR CATEGORIAS
    // =================================================

    dados.categorias.forEach(categoria => {

        let imagem;


        // Se tiver imagem cadastrada
        if (categoria.imagem) {

            imagem = `
                <img
                    src="../../imagens/${escaparHTML(categoria.imagem)}"
                    alt="${escaparHTML(categoria.nome)}"
                >
            `;

        }

        // Se não tiver imagem, usa imagem padrão
        else {

            imagem = `
                <img
                    src="../../imagens/icon-categorias.png"
                    alt="${escaparHTML(categoria.nome)}"
                >
            `;

        }


        const tr = document.createElement("tr");


        tr.innerHTML = `

            <td class="categoria">

                ${imagem}

                <div>

                    <strong>
                        ${escaparHTML(categoria.nome)}
                    </strong>

                </div>

            </td>


            <td>

                ${
                    categoria.descricao
                        ? escaparHTML(categoria.descricao)
                        : "Sem descrição"
                }

            </td>


            <td class="numero">

                ${Number(categoria.produtos) || 0}

            </td>


            <td>

                <span class="${
                    categoria.status === "Ativa"
                        ? "ativo-status"
                        : "inativo-status"
                }">

                    ${escaparHTML(
                        categoria.status || "Ativa"
                    )}

                </span>

            </td>


            <td>

                <div class="acoes">


                    <button
                        type="button"
                        class="editar"
                        onclick='abrirEdicao(${JSON.stringify(categoria)})'
                    >

                        <img
                            src="../../imagens/icon-editar.png"
                            alt="Editar"
                        >

                        Editar

                    </button>


                    <button
                        type="button"
                        class="lixeira"
                        onclick="excluirCategoria(${categoria.id_categoria})"
                    >

                        <img
                            src="../../imagens/icon-lixeira.png"
                            alt="Excluir"
                        >

                    </button>


                </div>

            </td>

        `;


        tabela.appendChild(tr);

    });


    atualizarCards(dados.categorias);


} catch (erro) {

    console.error("Erro:", erro);

    alert("Erro ao carregar categorias.");

}


}

// =====================================================
// SALVAR / EDITAR CATEGORIA
// =====================================================

form.addEventListener(
"submit",
async function(event) {


    event.preventDefault();

    const dados = new FormData(form);

    let url;


    if (idCategoria.value) {

        url = API_CATEGORIAS + "editar.php";

    } else {

        url = API_CATEGORIAS + "cadastrar.php";

    }


    try {

        const resposta = await fetch(
            url,
            {
                method: "POST",
                body: dados
            }
        );


        if (!resposta.ok) {

            throw new Error(
                "Erro HTTP: " + resposta.status
            );

        }


        const texto = await resposta.text();

        console.log(
            "Resposta do PHP:",
            texto
        );


        let resultado;


        try {

            resultado = JSON.parse(texto);

        } catch (erro) {

            console.error(
                "PHP não retornou JSON:",
                texto
            );

            alert(
                "O PHP retornou uma resposta inválida.\n\n" +
                "Veja o Console (F12) para descobrir o erro."
            );

            return;

        }


        alert(
            resultado.mensagem ||
            "Operação realizada."
        );


        if (resultado.sucesso) {

            fecharModal();

            await carregarCategorias();

        }


    } catch (erro) {

        console.error(
            "Erro:",
            erro
        );

        alert(
            "Erro ao salvar categoria."
        );

    }

}


);

// =====================================================
// ABRIR NOVA CATEGORIA
// =====================================================

function abrirNovaCategoria() {


form.reset();

idCategoria.value = "";

tituloModal.textContent =
    "Nova categoria";

statusCategoria.value =
    "Ativa";

modal.classList.add("mostrar");


}

// =====================================================
// ABRIR EDIÇÃO
// =====================================================

function abrirEdicao(categoria) {


idCategoria.value =
    categoria.id_categoria;

nomeCategoria.value =
    categoria.nome;

descricaoCategoria.value =
    categoria.descricao || "";

statusCategoria.value =
    categoria.status || "Ativa";

tituloModal.textContent =
    "Editar categoria";

modal.classList.add("mostrar");


}

// =====================================================
// FECHAR MODAL
// =====================================================

function fecharModal() {


modal.classList.remove("mostrar");

form.reset();

idCategoria.value = "";

tituloModal.textContent =
    "Nova categoria";


}

// =====================================================
// EXCLUIR CATEGORIA
// =====================================================

async function excluirCategoria(id) {


const confirmar = confirm(
    "Tem certeza que deseja excluir esta categoria?"
);


if (!confirmar) {
    return;
}


const dados = new FormData();

dados.append(
    "id_categoria",
    id
);


try {

    const resposta = await fetch(

        API_CATEGORIAS + "excluir.php",

        {
            method: "POST",
            body: dados
        }

    );


    if (!resposta.ok) {

        throw new Error(
            "Erro HTTP: " + resposta.status
        );

    }


    const resultado =
        await resposta.json();


    alert(
        resultado.mensagem ||
        "Categoria excluída."
    );


    if (resultado.sucesso) {

        await carregarCategorias();

    }


} catch (erro) {

    console.error(
        "Erro:",
        erro
    );

    alert(
        "Erro ao excluir categoria."
    );

}


}

// =====================================================
// ATUALIZAR CARDS
// =====================================================

function atualizarCards(categorias) {


const total =
    categorias.length;


const ativas =
    categorias.filter(
        categoria =>
            categoria.status === "Ativa"
    ).length;


const inativas =
    categorias.filter(
        categoria =>
            categoria.status === "Inativa"
    ).length;


let produtos = 0;


categorias.forEach(categoria => {

    produtos +=
        Number(categoria.produtos) || 0;

});


const elementoTotal =
    document.querySelector(
        "#totalCategorias"
    );


if (elementoTotal) {

    elementoTotal.textContent =
        total;

}


const elementoAtivas =
    document.querySelector(
        "#categoriasAtivas"
    );


if (elementoAtivas) {

    elementoAtivas.textContent =
        ativas;

}


const elementoInativas =
    document.querySelector(
        "#categoriasInativas"
    );


if (elementoInativas) {

    elementoInativas.textContent =
        inativas;

}


const elementoProdutos =
    document.querySelector(
        "#produtosVinculados"
    );


if (elementoProdutos) {

    elementoProdutos.textContent =
        produtos;

}


const porcentagemAtivas =
    total > 0
        ? Math.round(
            (ativas / total) * 100
        )
        : 0;


const porcentagemInativas =
    total > 0
        ? Math.round(
            (inativas / total) * 100
        )
        : 0;


const elementoPorcentagemAtivas =
    document.querySelector(
        "#porcentagemAtivas"
    );


if (elementoPorcentagemAtivas) {

    elementoPorcentagemAtivas.textContent =
        porcentagemAtivas + "% do total";

}


const elementoPorcentagemInativas =
    document.querySelector(
        "#porcentagemInativas"
    );


if (elementoPorcentagemInativas) {

    elementoPorcentagemInativas.textContent =
        porcentagemInativas + "% do total";

}


}

// =====================================================
// PESQUISA
// =====================================================

busca.addEventListener(
"input",
carregarCategorias
);

// =====================================================
// FILTRO DE STATUS
// =====================================================

filtroStatus.addEventListener(
"change",
carregarCategorias
);

// =====================================================
// FECHAR MODAL CLICANDO FORA
// =====================================================

modal.addEventListener(
"click",
function(event) {


    if (event.target === modal) {

        fecharModal();

    }

}


);

// =====================================================
// FECHAR COM ESC
// =====================================================

document.addEventListener(
"keydown",
function(event) {


    if (
        event.key === "Escape" &&
        modal.classList.contains("mostrar")
    ) {

        fecharModal();

    }

}


);

// =====================================================
// ESCAPAR HTML
// =====================================================

function escaparHTML(texto) {


const div =
    document.createElement("div");

div.textContent =
    texto ?? "";

return div.innerHTML;


}

// =====================================================
// INICIAR
// =====================================================

carregarCategorias();
