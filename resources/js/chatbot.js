class ChatBot {

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/listagemChatBot',
            type: 'POST',
            async: false,
            success: function (data) {

                var objData1 = JSON.parse(data)

                var template = document.getElementById('listChatBotTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData1);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listChatBot').innerHTML = rendered;
            }
        })
    }

    saveChatbot() {

        var select = document.getElementById("selListIntegrations");
        var integracao = select.value;

        var nomeValido = objMain.validar(document.getElementById("nome").value, "#formChatBot")
        var integracaoValida = objMain.validar(document.getElementById("selListIntegrations").value, "#formChatBot")

        if (nomeValido && integracaoValida) {
            $.ajax({
                url: '/saveChatbot',
                type: 'POST',
                data: {
                    nome: $('#nome').val(),
                    phone: integracao,
                    objJson: graph.toJSON(),
                    id: $('#idChatBot').val()
                },
                success: function (data) {
                    var jsonData = JSON.parse(data);
                    // Processa a resposta do servidor
                    if (jsonData.success) {
                        Swal.fire({
                            title: "Sucesso!",
                            text: "ChatBot configurado, agora é a hora de testar!",
                            icon: "success"
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: "Ops!",
                            text: "Nenhum usuário encontrado.",
                            icon: "error"
                        }).then((result) => {
                            if (result.isConfirmed || result.isDismissed) {
                                location.reload();
                            }
                        });
                    }
                },
                error: function (error) {
                    var jsonData = JSON.parse(data);

                }
            })

        }
    }

    edit(intId) {

        $.ajax({
            url: '/getChatbot',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {

                var objData = JSON.parse(data)

                console.log(objData)
                $('#nome').val(objData.data.nome)
                $('#selListIntegrations').val(objData.data.integration_phone)
                $('#idChatBot').val(objData.data.id)

                graph.fromJSON(JSON.parse(objData.data.json))
            }
        })
    }

    delete(intId) {

        function deletar(id) {
            $.ajax({
                url: '/deleteChatbot',
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
            "Digite 'excluir permanentemente' para excluir o registro:", function() {deletar(intId)} )

    }
}