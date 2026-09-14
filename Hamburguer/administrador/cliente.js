const URL_CLIENTES =
    "../Crud/clientes/listar.php";

const URL_EDITAR =
    "../Crud/clientes/editar.php";

const URL_EXCLUIR =
    "../Crud/clientes/excluir.php";


let clientes = [];

let clienteParaExcluir = null;

async function carregarClientes() {

    const lista =
        document.getElementById("listaClientes");

    try {

        const resposta =
            await fetch(URL_CLIENTES);

        const dados =
            await resposta.json();

        if (!dados.sucesso) {

            lista.innerHTML = `
                <tr>
                    <td colspan="7" class="erro">
                        ${dados.mensagem}
                    </td>
                </tr>
            `;

            return;
        }

        clientes = dados.clientes;

        atualizarResumo(dados.resumo);

        mostrarClientes(clientes);

    } catch (erro) {

        console.error(erro);

        lista.innerHTML = `
            <tr>
                <td colspan="7" class="erro">
                    Erro ao conectar com o servidor.
                </td>
            </tr>
        `;
    }
}

function mostrarClientes(lista) {

    const tabela =
        document.getElementById("listaClientes");

    tabela.innerHTML = "";


    if (lista.length === 0) {

        tabela.innerHTML = `
            <tr>
                <td colspan="7" class="vazio">
                    Nenhum cliente encontrado.
                </td>
            </tr>
        `;

        return;
    }


    lista.forEach(cliente => {

        const tr =
            document.createElement("tr");


        const dataCadastro =
            formatarData(cliente.data_cadastro);


        const ultimoPedido =
            cliente.ultimo_pedido
                ? formatarData(cliente.ultimo_pedido)
                : "Nenhum pedido";


        tr.innerHTML = `

            <td>

                <div class="cliente">

                    <div class="avatar">
                        ${primeiraLetra(cliente.nome)}
                    </div>

                    <div>

                        <strong>
                            ${escapeHtml(cliente.nome)}
                        </strong>

                        <small>
                            Cadastrado em ${dataCadastro}
                        </small>

                    </div>

                </div>

            </td>


            <td>

                <div class="contato">

                    <span>
                        ${escapeHtml(cliente.telefone || "-")}
                    </span>

                    <small>
                        ${escapeHtml(cliente.email)}
                    </small>

                </div>

            </td>


            <td>
                ${cliente.total_pedidos}
            </td>


            <td>
                R$ ${formatarNumero(cliente.total_gasto)}
            </td>


            <td>
                ${ultimoPedido}
            </td>


            <td>

                <span class="status ${cliente.status.toLowerCase()}">
                    ${cliente.status}
                </span>

            </td>


            <td>

                <div class="acoes">

                    <button
                        class="btn-editar"
                        onclick="abrirEditar(${cliente.id})"
                        title="Editar cliente">

                        <img
                            src="../../imagens/icon-editar.png"
                            alt="Editar">

                    </button>


                    <button
                        class="btn-excluir"
                        onclick="abrirExcluir(${cliente.id})"
                        title="Excluir cliente">

                        <img
                            src="../../imagens/icon-lixeira.png"
                            alt="Excluir">

                    </button>

                </div>

            </td>

        `;


        tabela.appendChild(tr);

    });
}

function atualizarResumo(resumo) {

    document.getElementById("totalClientes")
        .textContent = resumo.total_clientes;

    document.getElementById("clientesAtivos")
        .textContent = resumo.clientes_ativos;

    document.getElementById("novosClientes")
        .textContent = resumo.novos_clientes;

    document.getElementById("totalPedidos")
        .textContent = resumo.total_pedidos;
}

function abrirEditar(id) {

    const cliente =
        clientes.find(c => c.id == id);

    if (!cliente) {
        return;
    }


    document.getElementById("editarId")
        .value = cliente.id;

    document.getElementById("editarNome")
        .value = cliente.nome || "";

    document.getElementById("editarEmail")
        .value = cliente.email || "";

    document.getElementById("editarTelefone")
        .value = cliente.telefone || "";

    document.getElementById("editarCpf")
        .value = cliente.cpf || "";

    document.getElementById("editarEndereco")
        .value = cliente.endereco || "";


    document.getElementById("modalEditar")
        .style.display = "flex";
}


function fecharModalEditar() {

    document.getElementById("modalEditar")
        .style.display = "none";
}

document
    .getElementById("formEditar")
    .addEventListener("submit", async function(event) {

        event.preventDefault();


        const dados = {

            id: document.getElementById("editarId").value,

            nome: document.getElementById("editarNome").value,

            email: document.getElementById("editarEmail").value,

            telefone: document.getElementById("editarTelefone").value,

            cpf: document.getElementById("editarCpf").value,

            endereco: document.getElementById("editarEndereco").value

        };


        try {

            const resposta =
                await fetch(URL_EDITAR, {

                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded"
                    },

                    body:
                        new URLSearchParams(dados)

                });


            const resultado =
                await resposta.json();


            if (!resultado.sucesso) {

                alert(resultado.mensagem);

                return;
            }


            fecharModalEditar();

            await carregarClientes();


            alert("Cliente atualizado com sucesso!");

        } catch (erro) {

            console.error(erro);

            alert(
                "Erro ao atualizar o cliente."
            );

        }

    });

function abrirExcluir(id) {

    clienteParaExcluir = id;

    document.getElementById("modalExcluir")
        .style.display = "flex";
}


function fecharModalExcluir() {

    clienteParaExcluir = null;

    document.getElementById("modalExcluir")
        .style.display = "none";
}


async function confirmarExclusao() {

    if (!clienteParaExcluir) {
        return;
    }


    try {

        const resposta =
            await fetch(URL_EXCLUIR, {

                method: "POST",

                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },

                body:
                    new URLSearchParams({
                        id: clienteParaExcluir
                    })

            });


        const resultado =
            await resposta.json();


        if (!resultado.sucesso) {

            alert(resultado.mensagem);

            return;
        }


        fecharModalExcluir();

        await carregarClientes();


        alert("Cliente excluído com sucesso!");


    } catch (erro) {

        console.error(erro);

        alert(
            "Erro ao excluir o cliente."
        );

    }

}

function filtrarClientes() {

    const texto =
        document
            .getElementById("pesquisaCliente")
            .value
            .toLowerCase()
            .trim();


    const resultado =
        clientes.filter(cliente => {

            return (

                cliente.nome
                    .toLowerCase()
                    .includes(texto)

                ||

                cliente.email
                    .toLowerCase()
                    .includes(texto)

                ||

                (cliente.telefone || "")
                    .toLowerCase()
                    .includes(texto)

            );

        });


    mostrarClientes(resultado);
}

function formatarNumero(valor) {

    return Number(valor || 0)
        .toLocaleString("pt-BR", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

}


function formatarData(data) {

    if (!data) {
        return "-";
    }


    const partes =
        data.split(" ")[0]
            .split("-");


    if (partes.length !== 3) {
        return data;
    }


    return `${partes[2]}/${partes[1]}/${partes[0]}`;

}


function primeiraLetra(nome) {

    if (!nome) {
        return "?";
    }

    return nome
        .trim()
        .charAt(0)
        .toUpperCase();

}


function escapeHtml(texto) {

    const div =
        document.createElement("div");

    div.textContent =
        texto ?? "";

    return div.innerHTML;

}

window.addEventListener(
    "click",
    function(event) {

        const modalEditar =
            document.getElementById("modalEditar");

        const modalExcluir =
            document.getElementById("modalExcluir");


        if (event.target === modalEditar) {
            fecharModalEditar();
        }


        if (event.target === modalExcluir) {
            fecharModalExcluir();
        }

    }
);

document.addEventListener(
    "DOMContentLoaded",
    carregarClientes
);