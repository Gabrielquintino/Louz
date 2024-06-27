class Crm {

    constructor() {
        this.kanbam();
    }

    etapasLength = 0;
    arrEtapas = {};

    kanbam() {
        $('#listClients').hide();
        $('#kanbamClients').show();

        $.ajax({
            url: '/crm/kanbam',
            type: 'POST',
            data: {},
            success: function (data) {
                var objData = JSON.parse(data);
                var template = document.getElementById('kanbamTemplate').innerHTML;
                var compiled_template = Handlebars.compile(template);
                var rendered = compiled_template(objData);
                document.getElementById('kanbam').innerHTML = rendered;

                this.etapasLength = objData.etapas.length;
                this.arrEtapas = objData.etapas;

                var arrLog = objData.log
                arrLog.forEach(dados => {
                    console.log(dados)
                    var kanbanCard = document.createElement("div");
                    kanbanCard.id = dados.id;
                    kanbanCard.classList.add("kanban-card");
                    kanbanCard.setAttribute("draggable", "true");

                    var nomeElement = document.createElement("h5");
                    nomeElement.textContent = dados.nome;

                    var telefoneElement = document.createElement("p");
                    telefoneElement.textContent = "Telefone: " + dados.telefone;
                    telefoneElement.style.fontSize = "small";

                    kanbanCard.appendChild(nomeElement);
                    kanbanCard.appendChild(telefoneElement);

                    if (dados.email !== null) {
                        var emailElement = document.createElement("p");
                        emailElement.textContent = "Email: " + dados.email;
                        emailElement.style.fontSize = "small";
                        kanbanCard.appendChild(emailElement);
                    }

                    if (dados.tags !== null) {
                        var tagsElement = document.createElement("p");
                        tagsElement.textContent = "Tags: " + dados.tags;
                        tagsElement.style.fontSize = "small";
                        kanbanCard.appendChild(tagsElement);
                    }

                    var coluna = document.querySelector("#coluna-" + dados.etapa_order);
                    coluna.appendChild(kanbanCard);
                });

                objData.etapas.forEach(etapa => {
                    var colunaContent = document.getElementById('coluna-' + etapa.order);
                    new Sortable(colunaContent, {
                        group: 'shared',
                        animation: 150,
                        ghostClass: 'dragging',
                        onEnd: function (/**Event*/evt) {
                            var itemEl = evt.item;  // O elemento que foi movido
                            var targetColumn = itemEl.parentNode;  // A coluna de destino
                            var clientId = itemEl.id
                            var etapaId = targetColumn.id.match(/\d+/)[0];

                            objCrm.saveKanbam(clientId, etapaId)
                        }
                    });
                });
            }.bind(this)
        })
    }

    editKanbamStep(pEtapaId = null) {
        if (pEtapaId == null) {
            var template = document.getElementById('kanbamEtapaContentTemplate').innerHTML;
            var compiled_template = Handlebars.compile(template);
            var rendered = compiled_template();
            document.getElementById('kanbamEtapaContent').innerHTML = rendered;
    
            var ordem = document.getElementById('ordemEtapa');
            ordem.setAttribute("max", objCrm.etapasLength + 1);
    
            $('#kanbamEtapaModal').modal('show')
    
    
            $.ajax({
                url: '/chatbot/listagem',
                type: 'POST',
                success: function (data) {
    
                    var objData = JSON.parse(data)
    
                    var chatbots = objData.data;
    
                    objMain.inicializarSelect2('#formEtapaKanbam #chatbot', chatbots, "nome", true, '', '#kanbamEtapaModal');
                }
            })

            $('#excluirEtapa').hide();

            document.getElementById('kanbamModalTitle').innerHTML = "Nova Etapa";
        } else {

            var template = document.getElementById('kanbamEtapaContentTemplate').innerHTML;
            var compiled_template = Handlebars.compile(template);
            var rendered = compiled_template(this.arrEtapas[pEtapaId]);
            document.getElementById('kanbamEtapaContent').innerHTML = rendered;
    
            var ordem = document.getElementById('ordemEtapa');
            ordem.setAttribute("max", objCrm.etapasLength + 1);

            $('#excluirEtapa').show();
    
            $('#kanbamEtapaModal').modal('show')
    
    
            $.ajax({
                url: '/chatbot/listagem',
                type: 'POST',
                success: function (data) {
    
                    var objData = JSON.parse(data)
    
                    var chatbots = objData.data;
    
                    objMain.inicializarSelect2('#formEtapaKanbam #chatbot', chatbots, "nome", true, '', '#kanbamEtapaModal');
                }
            })

            document.getElementById('kanbamModalTitle').innerHTML = "Editar Etapa";

            setTimeout(() => {
                $('#chatbot').val(this.arrEtapas[pEtapaId].chatbot_id).trigger('change');
            }, 200);
        }
    }

    saveEtapa() {
        if (
            !objMain.validar(document.getElementById('nomeEtapa'), '#formEtapaKanbam') ||
            !objMain.validar(document.getElementById('chatbot'), '#formEtapaKanbam') ||
            !objMain.validar(document.getElementById('ordemEtapa'), '#formEtapaKanbam')
        ) {
            return false;
        }
        $.ajax({
            url: '/crm/saveEtapa',
            type: 'POST',
            data: {
                nome: $('#nomeEtapa').val(),
                chatbot_id: $('#chatbot').val(),
                order: $('#ordemEtapa').val()
            },
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Nova etapa cadastrada com sucesso.",
                    icon: "success"
                }).then((result) => {
                    location.reload();
                });
            },
            error: function (data) {
                Swal.fire({
                    title: "Ops!",
                    text: "Não foi possível salvar a etapa verifique o nome e a ordem.",
                    icon: "error"
                }).then((result) => {
                    return;
                });
            }
        }) 
    }

    deleteEtapa(pEtapaId) {
        var divs = document.querySelectorAll('div');
        var filteredDivs = Array.prototype.filter.call(divs, function(div) {
            return div.id.startsWith('coluna-');
        });

        var count = filteredDivs.length;
        
        if (count == 1) {
            Swal.fire({
                title: "Ops!",
                text: "Esta é a única etapa e por isso não pode ser excluída.",
                icon: "error"
            })
            return;
        }

        var clientesNaColuna = $('#coluna-' + pEtapaId).html();
        var booTemClinte = clientesNaColuna !== '\n        ' && clientesNaColuna !== undefined && clientesNaColuna !== null;
        var txtSucesso = "";
        var txtAviso = "";

        if (booTemClinte) {
            txtSucesso = "Etapa excluída e clientes movidos para nova etapa."
            var select = document.createElement('select');
            select.classList.add('form-control');
            select.id = "migrarParaEtapa";
            var option = document.createElement('option');
            option.value = "";
            option.innerHTML = "Selecione..."
            select.appendChild(option);
    
            this.arrEtapas.forEach(etapa => {
                if (etapa.id != pEtapaId) {
                    var option = document.createElement('option');
                    option.value = etapa.id;
                    option.innerHTML = etapa.nome;
                    select.appendChild(option);
                } else {
                    etapaNome = etapa.nome
                }
            })
    
            var selectElement = select.outerHTML;

            txtAviso = `
                <h4>Selecione uma outra etapa para migrar os clientes em ` + etapaNome + ` </h4>
            ` + selectElement;
        } else {
            txtSucesso = "Etapa excluída com sucesso."
            selectElement = "";
        }

        var etapaNome = "";
        
        Swal.fire({
            title: "Atenção esta ação é irreversivel",
            html: txtAviso,
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                var opcao = null
                if (booTemClinte) {
                    opcao = document.getElementById('migrarParaEtapa').value;
                }
                if (booTemClinte && opcao == '') {
                    Swal.showValidationMessage('Por favor, escolha uma nova etapa para os clientes.');
                } else {
                    return opcao;
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/crm/deleteEtapa',
                    type: 'POST',
                    data: {
                        newEtapaId: result.value,
                        etapaId: pEtapaId,
                        booTemClinte: booTemClinte
                    },
                    success: function (data) {            
                        Swal.fire({
                            title: "Sucesso!",
                            text: txtSucesso,
                            icon: "success"
                        }).then((result) => {
                            location.reload();
                        });
                    }
                })

            }
        })
    }

    saveKanbam(pClientId, pEtapaId) {
        // TODO:: verificar no log se possui mais que dez registros para o clienteId, se tiver... so deixa os ultimos dez

        $.ajax({
            url: '/crm/saveKanbam',
            type: 'POST',
            data: {
                clientId: pClientId,
                etapaId: pEtapaId
            },
            success: function (data) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "Cliente movido para nova etapa.",
                    icon: "success"
                })
            }
        })
    }

    list() {
        $('#kanbamClients').hide();
        $('#listClients').show();

        $.ajax({
            url: '/crm/listagem',
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

    edit(intId = null) {

        if (intId == null) {
            $('#formCrm').find('input[type=text], input[type=email], input[type=number], input[type=password], input[type=tel], input[type=url], textarea').val('');
            $('#dataCadastro').hide();
            $('#crmModal').modal('show');
        } else {
            $.ajax({
                url: '/crm/edit',
                type: 'POST',
                data: {
                    'id': intId
                },
                success: function (data) {

                    var objData = JSON.parse(data);

                    $('#formCrm #id').val(intId);
                    $('#formCrm #nome').val(objData.data.nome);
                    $('#formCrm #email').val(objData.data.email);
                    $('#formCrm #telefone').val(objData.data.telefone);
                    $('#formCrm #empresa').val(objData.data.empresa);
                    $('#formCrm #cargo').val(objData.data.cargo);
                    $('#formCrm #data').val(objData.data.data.split(" ")[0]);
                    $('#dataCadastro').show();
                    $('.bootstrap-tagsinput input').val(objData.data.tags);
                    $('#formCrm #tags').val(objData.data.tags);
                }
            })   
        }
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

                    $('#historicoCrm').show();

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