class Funcionario {
    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/funcionarios/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                console.log(objData)

                var template = document.getElementById('listFuncionariosTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listFuncionarios').innerHTML = rendered;
            }
        })
    }

    edit(intId) {
        $.ajax({
            url: '/funcionarios/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {
                var objData = JSON.parse(data);
                
                var template = document.getElementById('conteudoFuncionarioTemplate').innerHTML;

                var compiled_template = Handlebars.compile(template);

                var rendered = compiled_template(objData.data);

                document.getElementById('conteudoFuncionario').innerHTML = rendered;

                $.ajax({
                    url: '/cargos/listagem',
                    type: 'POST',
                    async: true,
                    success: function (data) {
                        var objData = JSON.parse(data);

                        objMain.inicializarSelect2("#formFuncionario #cargo", objData.data, 'nome', false, null, "#funcionarioModal", true)
                        
                    }
                })








            }
        })
    }

    save() {

        objMain.validar(document.getElementById('#nome'), '#formFuncionario');
        objMain.validar(document.getElementById('#email'), '#formFuncionario');
        objMain.validar(document.getElementById('#cargo'), '#formFuncionario');


        $.ajax({
            url: '/funcionarios/save',
            type: 'POST',
            data: {
                'id': $('#formFuncionario #id').val(),
                'nome': $('#formFuncionario #nome').val(),
                'email': $('#formFuncionario #email').val(),
                'cargo': $('#formFuncionario #cargo').val(),
                'comissao': $('#formFuncionario #comissao').val()
            },
            async: true,
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Funcionario salvo com sucesso.",
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
                url: '/funcionarios/delete',
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

        objMain.confirmWithInputAndCallback("Tem certeza? Ao excluir o Funcionario você exclui todos os atendimentos desse funcionario. Essa é uma ação irreversível.",
            "Digite 'excluir funcionario' para cancelar o agendamento:", function () { deletar(intId) }, 'excluir funcionario')
    }
}