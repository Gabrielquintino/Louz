class Produto {
    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/produtos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                console.log(objData)

                var template = document.getElementById('listProdutosTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listProdutos').innerHTML = rendered;
            }
        })
    }

    edit(intId = null) {

        function populaCargos(cargoSelected) {
            $.ajax({
                url: '/cargos/listagem',
                type: 'POST',
                async: true,
                success: function (data) {
                    var objData = JSON.parse(data);

                    objMain.inicializarSelect2("#formProduto #cargo", objData.data, 'nome', false, null, "#produtoModal", true);

                    $("#formProduto #cargo").val(cargoSelected);
                    $("#formProduto #cargo").trigger('change');
                }
            })
        }

        if (intId == null) {
            var template = document.getElementById('conteudoProdutoTemplate').innerHTML;

            var compiled_template = Handlebars.compile(template);

            var rendered = compiled_template(null);

            document.getElementById('conteudoProduto').innerHTML = rendered;

            populaCargos(0);
            
            return;
        }
        $.ajax({
            url: '/produtos/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {
                var objData = JSON.parse(data);
                
                var template = document.getElementById('conteudoProdutoTemplate').innerHTML;

                var compiled_template = Handlebars.compile(template);

                var rendered = compiled_template(objData.data);

                document.getElementById('conteudoProduto').innerHTML = rendered;

                const cargoSelected = objData.data.cargo_id;

                populaCargos(cargoSelected);
            }
        })
    }

    save() {

        if (
            !objMain.validar(document.getElementById('nome'), '#formProduto') || 
            !objMain.validar(document.getElementById('email'), '#formProduto')
        ) {
            return false;
        }

        if ($('#formProduto #cargo').val() == 0) {
            Swal.fire({title: "Ops!", text: "Selecione o cargo do funcionario", icon: "error"})
            return false;
        }


        $.ajax({
            url: '/produtos/save',
            type: 'POST',
            data: {
                'id': $('#formProduto #id').val(),
                'nome': $('#formProduto #nome').val(),
                'descricao': $('#formProduto #descricao').val(),
                'valor': $('#formProduto #valor').val()
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
                url: '/produtos/delete',
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
            "Digite 'excluir produto' para excluir:", function () { deletar(intId) }, 'excluir produto')
    }
}