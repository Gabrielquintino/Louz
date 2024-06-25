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
        if (intId == null) {
            var template = document.getElementById('conteudoProdutoTemplate').innerHTML;
            var compiled_template = Handlebars.compile(template);
            var rendered = compiled_template(null);
            document.getElementById('conteudoProduto').innerHTML = rendered;
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

                document.getElementById('descricao').value = objData.data.descricao;
            }
        })
    }

    save() {

        if (
            !objMain.validar(document.getElementById('nome'), '#formProduto') || 
            !objMain.validar(document.getElementById('valor'), '#formProduto') ||
            !objMain.validar(document.getElementById('descricao'), '#formProduto')
        ) {
            return false;
        }

        try {
            var floValue = objMain.convertToDecimal($('#formProduto #valor').val());
        } catch (error) {
            Swal.fire({
                title: "Ops!",
                text: "O valor não foi digitado corretamente.",
                icon: "error"
            })
            return;
        }


        $.ajax({
            url: '/produtos/save',
            type: 'POST',
            data: {
                'id': $('#formProduto #id').val(),
                'nome': $('#formProduto #nome').val(),
                'descricao': $('#formProduto #descricao').val(),
                'valor': floValue
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