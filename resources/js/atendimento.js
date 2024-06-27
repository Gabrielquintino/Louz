class Atendimento {

    constructor() {
        this.list();
    }

    intPage = 0;

    list(pIntPage = 1) {

        this.intPage = pIntPage;

        $.ajax({
            url: '/atendimento/listagem',
            type: 'POST',
            data: {
                page: pIntPage
            },
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

                setTimeout(function() {
                    $('#atendimento-0').click();

                }, 300)
            }
        })
    }

    history(pIntId, pStrTelefone, boolForceOpen = false) {

        $.ajax({
            url: '/crm/history',
            type: 'POST',
            data: {
                'id': parseInt(pIntId),
                'telefone': pStrTelefone
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


                    var myPhoto = document.getElementsByClassName('photo-fromMe')
                    var contactPhoto = document.getElementsByClassName('photo-client')

                    myPhoto.forEach(function(photo){
                        photo.src = objData.data.clientPhoto
                    })

                    contactPhoto.forEach(function(photo){
                        photo.src = objData.data.contactPhoto
                    })


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

    async edit(intId, clientId, strTelefone) {

        var template = document.getElementById('detalhesClienteTemplate').innerHTML;
        var compiled_template = Handlebars.compile(template);
        var rendered = compiled_template(null);
        document.getElementById('detalhesCliente').innerHTML = rendered;

        objMain.limparFormulario('formAtendimento');

        await $.ajax({
            url: '/funcionarios/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                var funcionarios = objData.data;

                objMain.inicializarSelect2('#formAtendimento #funcionarios', funcionarios, "nome", true, 'cargo', '#offcanvasRight');
            }
        })

        await $.ajax({
            url: '/eventos/listagem',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData = JSON.parse(data)

                var eventos = objData.data;

                objMain.inicializarSelect2('#formAtendimento #evento', eventos, "nome", false, null, '#offcanvasRight');
            }
        })

        $('#formAtendimento #telefone').val(strTelefone);
        $('#formAtendimento #id').val(intId);
        $('#formAtendimento #clientId').val(clientId);


        if (intId != null) {
            $.ajax({
                url: '/crm/edit',
                type: 'POST',
                data: {
                    'id': clientId
                },
                success: function (data) {
                    var objData = JSON.parse(data);
                    var template = document.getElementById('chatAtendimentoAcoesTemplate').innerHTML;
                    var compiled_template = Handlebars.compile(template);
                    var rendered = compiled_template(objData.data);
                    document.getElementById('chatAtendimentoAcoes').innerHTML = rendered;

                    var objDateTime = new DateTime();
                    objDateTime.picker('#id_0');
                    $('#formAtendimento #tags').tagsinput()

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

            this.history(intId, strTelefone, true);

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
        var observacao = $('#formAtendimento #observacao').val();


        if (
            $('#formAtendimento #funcionarios').val() == undefined &&
            $('#formAtendimento #agendamento').val() == '' &&
            $('#formAtendimento #originalTags').val() == $('#formAtendimento #tags').val() &&
            $('#formAtendimento #observacao').val() == ''
        ) {
            Swal.fire({
                title: "Ops!",
                text: "Nenhum campo foi alterado.",
                icon: "error"
            })

            return;
        }

        var data = dataHora

        if ($('#formAtendimento #evento').val() !== '') {
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
                'observacao': observacao,
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
        var strMessage = $('#message').val();
        var strTelefone = $('#formAtendimento #telefone').val();

        var img = document.getElementsByClassName('photo-fromMe');
        var src = img[0].src

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

                    <img src="` + src + `" alt="avatar 1"
                        style="width: 45px; height: 100%; border-radius: 100%">
                </div>`;

                $('#message').val('')
                var chatContainer = document.getElementById('chatAtendimento');
                chatContainer.scrollTop = chatContainer.scrollHeight;

            }
        })
    }

    destacar (pIntId, pClientId, pStrTelefone, card) {
        var cards = document.querySelectorAll('#listAtendimento .card');
        cards.forEach(function(card) {
            card.classList.remove('active');
        });
        
        // Add the 'active' class to the clicked card
        card.classList.add('active');

        setTimeout(() => {
            this.edit(pIntId, pClientId, pStrTelefone)            
        }, 300);
    }

    showActions() {

        var chatList = document.getElementById('listaChats');
        var chat = document.getElementById('chatAtendimentoConversa');
        var btn = document.getElementById('btnShowActions');
        var ico = document.getElementById('icoShowActions');

        if ($('#chatAtendimentoAcoes').is(':visible')) {
            $('#chatAtendimentoAcoes').hide()
            chatList.style.display = 'block';
            chat.classList = "col-12 col-sm-12 col-md-12 col-lg-8 col-xl-8";
            ico.classList = "ti ti-layout-sidebar-right-expand";
            $('#btnShowActions').attr('title', 'Expandir detalhes');
        } else {
            chatList.style.display = 'none';
            chat.classList = "col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6"
            $('#btnShowActions').attr('title', 'Esconder detalhes');

            ico.classList = "ti ti-layout-sidebar-left-expand";

            $('#chatAtendimentoAcoes').show();
        }
    }
}