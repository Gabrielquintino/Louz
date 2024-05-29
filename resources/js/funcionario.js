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

    edit(intId = null) {

        function populaCargos(cargoSelected) {
            $.ajax({
                url: '/cargos/listagem',
                type: 'POST',
                async: true,
                success: function (data) {
                    var objData = JSON.parse(data);

                    objMain.inicializarSelect2("#formFuncionario #cargo", objData.data, 'nome', false, null, "#funcionarioModal", true);

                    $("#formFuncionario #cargo").val(cargoSelected);
                    $("#formFuncionario #cargo").trigger('change');
                }
            })
        }

        if (intId == null) {
            var template = document.getElementById('conteudoFuncionarioTemplate').innerHTML;

            var compiled_template = Handlebars.compile(template);

            var rendered = compiled_template(null);

            document.getElementById('conteudoFuncionario').innerHTML = rendered;

            populaCargos(0);
            
            return;
        }
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

                const cargoSelected = objData.data.cargo_id;

                populaCargos(cargoSelected);
            }
        })
    }

    async deleteCargo() {
        if ($("#formFuncionario #cargo").val() == '') {
            Swal.fire({
                title: "Ops!",
                text: "Selecione um cargo para excluir",
                icon: "erros"
            })
            return;
        }
        const cargoSelect = document.getElementById('cargo');
        const selectedValue = cargoSelect.value;
        
        // Cria um novo select com as mesmas opções, exceto a selecionada
        const newCargoSelect = document.createElement('select');
        newCargoSelect.id = 'newCargo';
        newCargoSelect.classList.add('form-control');

        const h4 = document.createElement('h4');
        h4.innerHTML = "Para excluir este cargo, e necessario migrar os funcionarios para um outro cargo. Selecione o novo cargo abaixo.";
    
        for (let option of cargoSelect.options) {
            if (option.value !== selectedValue) {
                const newOption = document.createElement('option');
                newOption.value = option.value;
                newOption.text = option.text;
                newCargoSelect.appendChild(newOption);
            }
        }
    
        // Cria um contêiner div para o novo select
        const container = document.createElement('div');
        container.appendChild(h4);
        container.appendChild(newCargoSelect);
    
        // Exibe o SweetAlert com o novo select
        Swal.fire({
            title: 'Atenção!',
            html: container,
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                const selectedNewCargo = newCargoSelect.value;

                if (selectedNewCargo == '0') {
                    Swal.fire({
                        title: "Ops!",
                        text: "Escolha um novo cargo.",
                        icon: "error"
                    }).then((result) => {
                        return;
                    });
                } else {
                    $.ajax({
                        url: '/cargos/delete',
                        type: 'POST',
                        data: {
                            'id': selectedValue,
                            'newId': selectedNewCargo
                        },
                        async: true,
                        success: function (data) {
                            Swal.fire({
                                title: "Sucesso!",
                                text: "Funcionario salvo com sucesso.",
                                icon: "success"
                            }).then((result) => {
                                $('#formFuncionario #cargo').val(selectedNewCargo).trigger('change');
                            });
                        }
                    })
                }                
            }
        });


    }

    save() {

        if (
            !objMain.validar(document.getElementById('nome'), '#formFuncionario') || 
            !objMain.validar(document.getElementById('email'), '#formFuncionario')
        ) {
            return false;
        }

        if ($('#formFuncionario #cargo').val() == 0) {
            Swal.fire({title: "Ops!", text: "Selecione o cargo do funcionario", icon: "error"})
            return false;
        }


        $.ajax({
            url: '/funcionarios/save',
            type: 'POST',
            data: {
                'id': $('#formFuncionario #id').val(),
                'nome': $('#formFuncionario #nome').val(),
                'email': $('#formFuncionario #email').val(),
                'cargos_id': $('#formFuncionario #cargo').val(),
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