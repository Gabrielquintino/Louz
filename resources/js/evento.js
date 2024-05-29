class Evento {
    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/eventos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                console.log(objData)

                var template = document.getElementById('listEventoTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listEventos').innerHTML = rendered;
            }
        })
    }

    edit(intId) {
        $.ajax({
            url: '/eventos/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {
                var objData = JSON.parse(data);

                console.log(objData)
                
                var template = document.getElementById('conteudoEventoTemplate').innerHTML;

                var compiled_template = Handlebars.compile(template);

                var rendered = compiled_template(objData.data);

                document.getElementById('conteudoEvento').innerHTML = rendered;

                var objDateTime = new DateTime();
                objDateTime.picker('#formEvento #dataInicioEvento');
                objDateTime.picker('#formEvento #dataFimEvento');
            }
        })
    }

    save() {

        objMain.validar(document.getElementById('#dataInicioEvento'), '#nome');


        var data = $('#formEvento #dataInicioEvento').val()
        var dataFornecida = new Date(data);
        var dataAtual = new Date();
        if (dataFornecida < dataAtual) {
            Swal.fire({
                title: "Ops!",
                text: "A data de iníncio é menor que a data atual.",
                icon: "error"
            })
        }

        var data = $('#formEvento #dataFimEvento').val()
        var dataFornecida = new Date(data);
        var dataAtual = new Date();
        if (dataFornecida < dataAtual) {
            Swal.fire({
                title: "Ops!",
                text: "A data final é menor que a data atual.",
                icon: "error"
            })
        }

        $.ajax({
            url: '/eventos/save',
            type: 'POST',
            data: {
                'id': $('#formEvento #id').val(),
                'nome': $('#formEvento #nome').val(),
                'data_inicio': $('#formEvento #dataInicio').val(),
                'data_fim': $('#formEvento #dataFim').val(),
                'periodicidade': $('#formEvento #periodicidade').val()
            },
            async: true,
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Evento salvo com sucesso.",
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
                url: '/eventos/delete',
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

        objMain.confirmWithInputAndCallback("Tem certeza? Ao excluir o evento você exclui todos os agendamentos com esse evento. Essa é uma ação irreversível.",
            "Digite 'excluir evento' para cancelar o agendamento:", function () { deletar(intId) }, 'excluir evento')
    }
}