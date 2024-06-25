class Venda {

    arrProdutos = [];

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/vendas/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data);
                var template = document.getElementById('listVendasTemplate').innerHTML;
                var compiled_template = Handlebars.compile(template);
                var rendered = compiled_template(objData);
                document.getElementById('listVendas').innerHTML = rendered;
            }
        })
    }

    async edit(intId = null) {

        var template = document.getElementById('conteudoVendaTemplate').innerHTML;
        var compiled_template = Handlebars.compile(template);
        var rendered = compiled_template(null);
        document.getElementById('conteudoVenda').innerHTML = rendered;

        await $.ajax({
            url: '/funcionarios/listagem',
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)
                var funcionarios = objData.data;
                objMain.inicializarSelect2('#formVenda #funcionarios', funcionarios, "nome", true, 'cargo', '#vendaModal');
            }
        })

        await $.ajax({
            url: '/eventos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)
                var eventos = objData.data;
                objMain.inicializarSelect2('#formVenda #evento', eventos, "nome", false, null, '#vendaModal');
            }
        })

        await $.ajax({
            url: '/crm/listagem',
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)
                var clientes = objData.data;
                objMain.inicializarSelect2('#formVenda #cliente', clientes, "nome", false, null, '#vendaModal');
            }
        })

        await $.ajax({
            url: '/produtos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)
                var produtos = objData.data;
                objVenda.arrProdutos = produtos;
                objMain.inicializarSelect2('#formVenda #produto', produtos, "nome", false, null, '#vendaModal');
            }
        })

        await $.ajax({
            url: '/vendas/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {
                var objData = JSON.parse(data);

                setTimeout(() => {
                    $('#formVenda #id').val(objData.data[0].id);
                    $("#formVenda #evento").val(objData.data[0].evento_id);
                    $("#formVenda #evento").trigger('change');
                    $("#formVenda #produto").val(objData.data[0].produtos_id);
                    $("#formVenda #produto").trigger('change');
                    $("#formVenda #cliente").val(objData.data[0].cliente_id);
                    $("#formVenda #cliente").trigger('change');
                    $("#formVenda #funcionarios").val(objData.data[0].funcionario_id);
                    $("#formVenda #funcionarios").trigger('change');
                    $("#formVenda #condicao").val(objData.data[0].pagamento);
                    $("#formVenda #desconto").val(objData.data[0].desconto);
                    $("#formVenda #qtdItens").val(objData.data[0].qtd_itens);
                    $("#formVenda #qtdItens").trigger('change');
                    $("#formVenda #status").val(objData.data[0].status);

                    const options = {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        timeZoneName: 'short',
                    };
                
                    const formattedDate = new Date(objData.data[0].data).toLocaleString('pt-BR', options);
                    const dateWithoutTimeZone = formattedDate.replace(/ [A-Z]{3}$/, '');
                    var shortYearDate = dateWithoutTimeZone.replace(",", "")
                    console.log(dateWithoutTimeZone.replace(",", ""))

                    $("#formVenda #data").val(shortYearDate);


                }, 300);

            }
        })

        var objDateTime = new DateTime();
        objDateTime.picker('#dataVenda');
    }

    escondeParcela() {
        var selectElement = document.getElementById('condicao');
        var divElement = document.getElementById('parcelas');

        if (selectElement.value == 'credito') {
            divElement.style.display = 'block';
        } else {
            divElement.style.display = 'none';
        }
    }

    aplicaValor() {

        if (this.arrProdutos != null) {
            var qtd = $('#formVenda #qtdItens').val() == 0 ? 1 : $('#formVenda #qtdItens').val();
            var desconto = $('#formVenda #desconto').val() == 0 ? 0 : $('#formVenda #desconto').val();
            this.arrProdutos.forEach(produto => {
                if ( $('#formVenda #produto').val() == produto.id) {
                    var valor = produto.valor * qtd;
                    var descontoPorCento = (desconto / 100) * valor;
                    var total = valor - descontoPorCento;
                    $('#formVenda #valor').val(total.toFixed(2));
                }
            });
        }
    }

    save() {
        if (
            (!objMain.validar(document.getElementById('evento'), '#formVenda') &&
            !objMain.validar(document.getElementById('produto'), '#formVenda')) ||
            !objMain.validar(document.getElementById('cliente'), '#formVenda') ||
            !objMain.validar(document.getElementById('funcionarios'), '#formVenda') ||
            !objMain.validar(document.getElementById('condicao'), '#formVenda') ||
            !objMain.validar(document.getElementById('valor'), '#formVenda') ||
            !objMain.validar(document.getElementById('status'), '#formVenda')
        ) {
            alert('falha')
            return false;
        }

        try {
            var floValue = objMain.convertToDecimal($('#formVenda #valor').val());
            var floDesconto = objMain.convertToDecimal($('#formVenda #desconto').val());

        } catch (error) {
            Swal.fire({
                title: "Ops!",
                text: "O valor não foi digitado corretamente.",
                icon: "error"
            })
            return;
        }

        $.ajax({
            url: '/vendas/save',
            type: 'POST',
            data: {
                'id': $('#formVenda #id').val(),
                'evento_id': $('#formVenda #evento').val(),
                'produtos_id': $('#formVenda #produto').val(),
                'funcionario_id': $('#formVenda #funcionarios').val(),
                'atendimento_id':null,
                'cliente_id': $('#formVenda #cliente').val(),
                'pagamento': $('#formVenda #condicao').val(),
                'condicao': $('#formVenda #parcelas').val(),
                'total': floValue.toFixed(2),
                'desconto':floDesconto.toFixed(2),
                'qtd_itens': $('#formVenda #qtdItens').val(),
                'data': $('#formVenda #data').val(),
                'status': $('#formVenda #statusstatus').val(),
            },
            async: true,
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Produto salvo com sucesso.",
                    icon: "success"
                }).then((result) => {
                    location.reload();
                });
            }
        })

    }

    delete(intId) {
        function deletar(id) {
            $.ajax({
                url: '/vendas/delete',
                type: 'POST',
                data: {
                    'id': id
                },
                async: true,
                success: function (data) {
                    var objData = JSON.parse(data);
                }
            })
        }

        objMain.confirmWithInputAndCallback("Tem certeza?",
            "Digite 'excluir venda' para excluir:", function () { deletar(intId) }, 'excluir venda')
    }
}