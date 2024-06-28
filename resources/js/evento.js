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

    edit(intId = null) {
        if (intId == null) {
            var template = document.getElementById('conteudoEventoTemplate').innerHTML;
    
            var compiled_template = Handlebars.compile(template);
    
            var rendered = compiled_template(null);
    
            document.getElementById('conteudoEvento').innerHTML = rendered;
    
            var objDateTime = new DateTime();
            objDateTime.picker('#formEvento #dataInicioEvento');
            objDateTime.picker('#formEvento #dataFimEvento');
    
            return;
        }
    
        $.ajax({
            url: '/eventos/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {
                var objData = JSON.parse(data);
                
                var template = document.getElementById('conteudoEventoTemplate').innerHTML;
    
                var compiled_template = Handlebars.compile(template);
    
                var rendered = compiled_template(objData.data);
    
                document.getElementById('conteudoEvento').innerHTML = rendered;
    
                var objDateTime = new DateTime();
                objDateTime.picker('#formEvento #dataInicioEvento');
                objDateTime.picker('#formEvento #dataFimEvento');
    
                // Preencher os campos de dias e horários da semana
                if (objData.data.dias_semana && objData.data.horarios_semana) {
                    var diasSemana = objData.data.dias_semana.split(', ');
                    var horariosSemana = JSON.parse(objData.data.horarios_semana);

                    console.log(diasSemana);
                    console.log(horariosSemana);

                    diasSemana.forEach(function(dia) {
                        dia = dia.toLowerCase();
                        if (document.getElementById(dia)) {
                            document.getElementById(dia).checked = true;
                            if (horariosSemana[dia.charAt(0).toUpperCase() + dia.slice(1)]) {
                                var horarios = horariosSemana[dia.charAt(0).toUpperCase() + dia.slice(1)].split('-');
                                if (horarios[0]) {
                                    document.querySelector(`input[name='${dia}_inicio']`).value = horarios[0];
                                }
                                if (horarios[1]) {
                                    document.querySelector(`input[name='${dia}_fim']`).value = horarios[1];
                                }
                            }
                        }
                    });
                }
            }
        })
    }
    

    save() {

        var valid = true;
        var errorMessage = "";

        // Validação de data de início e fim do evento
        var dataInicio = $('#dataInicio').val();
        var dataFim = $('#dataFim').val();
        if (new Date(dataInicio) < new Date()) {
            valid = false;
            errorMessage += "A data de início é menor que a data atual.\n";
        }
        if (new Date(dataFim) < new Date()) {
            valid = false;
            errorMessage += "A data final é menor que a data atual.\n";
        }
        if ($('#formEvento #valor').val() < 0) {
            valid = false;
            errorMessage += "O valor do evento não pode ser inferior a 0.\n";
        }

        // Validação dos dias da semana e horários
        var diasSelecionados = $('input[name="dias_semana[]"]:checked');
        if (diasSelecionados.length === 0) {
            valid = false;
            errorMessage += "Selecione pelo menos um dia da semana.\n";
        }

        diasSelecionados.each(function() {
            var dia = $(this).val();
            var inicio = $('input[name="' + dia + '_inicio"]').val();
            var fim = $('input[name="' + dia + '_fim"]').val();

            if (!inicio || !fim) {
                valid = false;
                errorMessage += "Preencha os horários de início e fim para " + dia + ".\n";
            } else if (inicio >= fim) {
                valid = false;
                errorMessage += "O horário de fim deve ser maior que o horário de início para " + dia + ".\n";
            }
        });

        if (!valid) {
            Swal.fire({
                title: "Erro!",
                text: errorMessage,
                icon: "error"
            });
            return;
        }

        // Dados para envio
        var dados = {
            'id': $('#id').val(),
            'valor': $('#formEvento #valor').val(),
            'nome': $('#nome').val(),
            'data_inicio': $('#dataInicio').val(),
            'data_fim': $('#dataFim').val(),
            'periodicidade': $('#periodicidade').val(),
            'duracao_horas': $('#duracao_horas').val(),
            'dias_semana': [],
            'horarios_semana': {}
        };

        diasSelecionados.each(function() {
            var dia = $(this).val();
            var inicio = $('input[name="' + dia + '_inicio"]').val();
            var fim = $('input[name="' + dia + '_fim"]').val();
            dados.dias_semana.push(dia);
            dados.horarios_semana[dia] = [inicio, fim];
        });

        $.ajax({
            url: '/eventos/save',
            type: 'POST',
            data: dados,
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
        });

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