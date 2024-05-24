
class Crm {

    constructor() {
        this.list();
    }

    list() {
        $.ajax({
            url: '/crm/list',
            type: 'POST',
            success: function (data) {
                var objData = JSON.parse(data)

                var template = document.getElementById('listCrmTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listCrm').innerHTML = rendered;
            }
        })
    }

    edit(intId) {

        $.ajax({
            url: '/crm/edit',
            type: 'POST',
            data: {
                'id': intId
            },
            success: function (data) {

                var objData = JSON.parse(data);
                console.log(objData);

                $('#formCrm #id').val(intId);
                $('#formCrm #nome').val(objData.data.nome);
                $('#formCrm #email').val(objData.data.email);
                $('#formCrm #telefone').val(objData.data.telefone);
                $('#formCrm #empresa').val(objData.data.empresa);
                $('#formCrm #cargo').val(objData.data.cargo);


                $('#formCrm #data').val(objData.data.data.split(" ")[0]);
                $('.bootstrap-tagsinput input').val(objData.data.tags);
                $('#formCrm #tags').val(objData.data.tags);

                $('#formCrm #crmModal').modal('show');

            }
        })
    }

    save() {
        $.ajax({
            url: '/crm/save',
            type: 'POST',
            data: $('#formCrm').serializeArray(),
            success: function (data) {

                Swal.fire({
                    title: "Sucesso!",
                    text: "Dados do cliente salvos com sucesso.",
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
                url: '/crm/delete',
                type: 'POST',
                data: {
                    'id': id
                },
                success: function (data) {
                    var objData = JSON.parse(data);
                    
                }
            })
        }

        objMain.confirmWithInputAndCallback("Tem certeza? Essa é uma ação irreversível.",
            "Digite 'excluir permanentemente' para excluir o registro:", function() {deletar(intId)} )

    }
    listChat() {
        $.ajax({
            url: '/crm/history',
            type: 'POST',
            data: {
                'id': $('#formCrm #id').val(),
                'telefone': $('#formCrm #telefone').val()
            },
            success: function (data) {
                var objData = JSON.parse(data);
                console.log(objData)
                if (objData) {
                    var template = document.getElementById('chatTemplate').innerHTML;
                    var compiled_template = Handlebars.compile(template);
                    var rendered = compiled_template(objData.data);
                    document.getElementById('chat').innerHTML = rendered;

                    var chatContainer = document.getElementById('chat');
                    // Rola a barra de rolagem para a parte inferior
                    chatContainer.scrollTop = chatContainer.scrollHeight;

                    var template = document.getElementById('listAvaliacoesTemplate').innerHTML;
                    var compiled_template = Handlebars.compile(template);
                    var rendered = compiled_template(objData.data);
                    document.getElementById('listAvaliacoes').innerHTML = rendered;
                }
            },
            error: function () {
                document.getElementById('chat').innerHTML = "<h4 class='text-center text-danger'>Erro ao carregar mensagens.</h4>";
            },
            fail: function(jqXHR, textStatus, errorThrown) {
                console.log("Erro na requisição Ajax:", textStatus, errorThrown);
                var compiled_template = Handlebars.compile("<h4 class='text-center text-danger'>Erro ao carregar mensagens.</h4>");
                var rendered = compiled_template();
                document.getElementById('chat').innerHTML = rendered;
            }
        });
    }
    
}