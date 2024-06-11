class Atendimento {

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/atendimento/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                var template = document.getElementById('listAtendimentoTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listAtendimento').innerHTML = rendered;

            }
        })
    }

    history(boolForceOpen = false) {

        var modal = document.querySelector('#atendimentotModal');
        var strTelefone = $('#formAtendimento #telefone').val();
        var intId = $('#formAtendimento #id').val();

        if ((modal.classList.contains('show') || boolForceOpen ) && strTelefone != '' && intId != '') {
            $.ajax({
                url: '/crm/history',
                type: 'POST',
                data: {
                    'id': parseInt(intId),
                    'telefone': strTelefone
                },
                success: function (data) {
                    var objData = JSON.parse(data);
                    if (objData) {
                        var template = document.getElementById('chatAtendimentoTemplate').innerHTML;
                        var compiled_template = Handlebars.compile(template);
                        var rendered = compiled_template(objData.data);
                        document.getElementById('chatAtendimento').innerHTML = rendered;

                        var chatContainer = document.getElementById('chatAtendimento');
                        // Rola a barra de rolagem para a parte inferior
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                },
                error: function () {
                    document.getElementById('chatAtendimento').innerHTML = "<h4 class='text-center text-danger'>Erro ao carregar mensagens.</h4>";
                },
                fail: function (jqXHR, textStatus, errorThrown) {
                    console.log("Erro na requisição Ajax:", textStatus, errorThrown);
                    var compiled_template = Handlebars.compile("<h4 class='text-center text-danger'>Erro ao carregar mensagens.</h4>");
                    var rendered = compiled_template();
                    document.getElementById('chat').innerHTML = rendered;
                }
            });
        }
    }

    edit(intId, clientId, strTelefone) {


        $('#formAtendimento #telefone').val(strTelefone);
        $('#formAtendimento #id').val(intId);
        $('#formAtendimento #clientId').val(clientId);


        $.ajax({
            url: '/funcionarios/listagem',
            type: 'POST',
            success: function (data) {

                var objData = JSON.parse(data)

                var funcionarios = objData.data;

                objMain.inicializarSelect2('#formAtendimento #funcionarios', funcionarios, "nome", true, 'cargo', '#atendimentotModal');
            }
        })

        $.ajax({
            url: '/eventos/listagem',
            type: 'POST',
            success: function (data) {

                var objData = JSON.parse(data)

                var eventos = objData.data;

                objMain.inicializarSelect2('#formAtendimento #evento', eventos, "nome", true, '', '#atendimentotModal');
            }
        })


        objMain.limparFormulario('formAtendimento');

        if (intId != null) {
            $.ajax({
                url: '/crm/edit',
                type: 'POST',
                data: {
                    'id': $('#formAtendimento #clientId').val()
                },
                success: function (data) {
                    var objData = JSON.parse(data);
                    if (objData.data.tags) {
                        var tagsArray = objData.data.tags.split(',');
                        $('#formAtendimento #originalTags').val(objData.data.tags);

                        // Itera sobre cada tag e adiciona ao input
                        tagsArray.forEach(function(tag) {
                            $('#formAtendimento #tags').tagsinput('add', tag);
                        });
                    }
                }
            })

            this.history(true);

        } else {
            document.getElementById('chatAtendimento').innerHTML = "<h4 class='text-center text-danger' style='margin-top: 125px;'>Sem mensagens.</h4>";
        }
    }

    save() {
        var intId = $('#formAtendimento #id').val();
        var clientId = $('#formAtendimento #clientId').val();
        var eventoId = $('#formAtendimento #evento ').val();
        var dataHora = $('#formAtendimento #agendamento').val();
        var funcionario = $('#formAtendimento #funcionarios').val();

        if (
            $('#formAtnedimento #funcionarios').val() == undefined &&
            $('#formAtendimento #agendamento').val() == '' &&
            $('#formAtendimento #originalTags').val() == $('#formAtendimento #tags').val()
        ) {
            Swal.fire({
                title: "Ops!",
                text: "Nenhum campo foi alterado.",
                icon: "error"
            })

            return;
        }

        var data = dataHora

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

            return;
        }

        var boolTag = $('#formAtendimento #originalTags').val() == $('#formAtendimento #tags').val();

        $.ajax({
            url: '/atendimento/save',
            type: 'POST',
            data: {
                'id': intId,
                'cliente_id': clientId,
                'evento_id': eventoId,
                'dataHora': dataHora,
                'funcionarios_id': funcionario,
                'tags': $('#formAtendimento #tags').val(),
                'boolTag': boolTag
            },
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Atendimento salvo com sucesso.",
                    icon: "success"
                }).then((result) => {
                    location.reload();
                });
            }
        })
    }

    encerrar() {
        var intId = $('#formAtendimento #id').val();
        var clientId = $('#formAtendimento #clientId').val();

        $.ajax({
            url: '/atendimento/encerrar',
            type: 'POST',
            data: {
                'id': intId,
                'clientId': clientId
            },
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Atendimento encerrado com sucesso.",
                    icon: "success"
                }).then((result) => {
                    location.reload();
                });
            }
        })
        
    }

    sendMessage() {
        var strMessage = $('#formAtendimento #message').val();
        var strTelefone = $('#formAtendimento #telefone').val();


        $.ajax({
            url: '/atendimento/sendMessage',
            type: 'POST',
            data: {
                'message': strMessage,
                'telefone': strTelefone
            },
            success: function (data) {

                function formatDate(date) {
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = String(date.getFullYear()).slice(-2);
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');

                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                }

                const now = new Date();
                const dataAgora = formatDate(now);


                document.getElementById('chatAtendimento').innerHTML = document.getElementById('chatAtendimento').innerHTML + `
                <div class="d-flex flex-row justify-content-end mb-4">
                    <div class="p-3 me-3 border"
                        style="border-radius: 15px; background-color: #fbfbfb;">
                        <p class="small mb-0">` + strMessage + `</p>
                        <p class="text-end mb-0" style="font-size: xx-small;">` + dataAgora + `</p>
                    </div>

                    <img src="../../resources/images/profile/chatbot.png" alt="avatar 1"
                        style="width: 45px; height: 100%;">
                </div>`;

                $('#formAtendimento #message').val('')
                var chatContainer = document.getElementById('chatAtendimento');
                chatContainer.scrollTop = chatContainer.scrollHeight;

            }
        })
    }
}