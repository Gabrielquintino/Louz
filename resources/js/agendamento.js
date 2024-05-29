class Agendamento {

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/agendamentos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                var template = document.getElementById('listAgendamentoTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listAgendamento').innerHTML = rendered;
            }
        })
    }

    edit(intId) {
        $.ajax({
            url: '/agendamentos/get',
            type: 'POST',
            data: {
                id: intId
            },
            async: false,
            success: function (data) {
                var objData = JSON.parse(data);
                
                var template = document.getElementById('conteudoAtendimentoTemplate').innerHTML;

                var compiled_template = Handlebars.compile(template);

                var rendered = compiled_template(objData.data);

                document.getElementById('conteudoAtendimento').innerHTML = rendered;

                var objDateTime = new DateTime();
                objDateTime.picker('#formAgendamento #dateEvento');
            }
        })
    }

    cancel(intId) {

        function deletar(id) {
            $.ajax({
                url: '/agendamentos/delete',
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

        objMain.confirmWithInputAndCallback("Tem certeza? Essa é uma ação irreversível.",
            "Digite 'cancelar evento' para cancelar o agendamento:", function () { deletar(intId) }, 'cancelar evento')

    }

    save() {
        var data = $('#formAgendamento #evento').val()

        objMain.validar(document.getElementById('#evento'), '#formAgendamento');

        const dataFornecida = new Date(data);

        // Data atual
        const dataAtual = new Date();

        // Verifica se a data fornecida é menor que a data atual
        if (dataFornecida < dataAtual) {
            Swal.fire({
                title: "Ops!",
                text: "A data fornecida e menor que a data atual.",
                icon: "error"
            })
        }
    }
}