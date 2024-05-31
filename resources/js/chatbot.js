class ChatBot {

    constructor() {
        this.list();
    }

    arrEventos = [];

    list() {

        $.ajax({
            url: '/chatbot/listagem',
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

        $.ajax({
            url: '/integracao/listagem',
            type: 'POST',
            success: function (data) {

                var objData = JSON.parse(data)

                objData.data.forEach(function (item) {
                    $('#selListIntegrations').append(
                        $('<option>', { value: item.phone, text: item.phone })
                    )
                })
            }
        })
    }

    saveChatbot() {

        var arrOrder = graph.getSuccessors(graph.getFirstCell());
        var arrOrderCompressed = JSON.stringify(arrOrder)
        var arrObjJsonCompressed = JSON.stringify(graph.toJSON())

        var select = document.getElementById("selListIntegrations");
        var integracao = select.value;

        var nomeValido = objMain.validar(document.getElementById("nome").value, "#formChatBot")
        var integracaoValida = objMain.validar(document.getElementById("selListIntegrations").value, "#formChatBot")

        if (nomeValido && integracaoValida) {

            $.ajax({
                url: '/chatbot/save',
                type: 'POST',
                data: {
                    nome: $('#nome').val(),
                    phone: integracao,
                    objJson: arrObjJsonCompressed,
                    arrOrder: arrOrderCompressed,
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
            url: '/chatbot/get',
            type: 'POST',
            data: {
                'id': intId
            },
            async: true,
            success: function (data) {

                var objData = JSON.parse(data)

                $('#nome').val(objData.data.nome)
                $('#selListIntegrations').val(objData.data.integration_phone)
                $('#idChatBot').val(objData.data.id)

                setTimeout(function () {
                    graph.fromJSON(JSON.parse(objData.data.json))
                }, 4000)

                $('#chatbotModal').modal('show');
            }
        })
    }

    delete(intId) {

        function deletar(id) {
            $.ajax({
                url: '/chatbot/delete',
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
            "Digite 'excluir permanentemente' para excluir o registro:", function () { deletar(intId) })

    }

    async uploadToAWS(pFileInput) {
        return new Promise((resolve, reject) => {
            // Selecionar o input do arquivo
            var fileInput = document.getElementById(pFileInput);

            // Criar um objeto FormData
            var formData = new FormData();

            // Adicionar o arquivo ao objeto FormData
            formData.append('file', fileInput.files[0]);

            $.ajax({
                url: '/chatbot/upload', // URL do seu backend
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    var objJson = JSON.parse(data);
                    if (objJson.success) {
                        resolve(objJson.link);
                    } else {
                        Swal.fire({
                            title: "Ops!",
                            text: objJson.message,
                            icon: "error"
                        })
                        reject("Erro ao enviar arquivo");                        
                    }
                },
                error: function (err) {
                    Swal.fire({
                        title: "Ops!",
                        text: err,
                        icon: "error"
                    })
                    console.error('Erro ao enviar arquivo:', err);
                    reject(err);
                }
            });
        });
    }
}

var graph = new joint.dia.Graph;
var paper = new joint.dia.Paper({
    el: document.getElementById('paper'),
    gridSize: 1,
    width: 1320,
    height: 520,
    model: graph,
    snapLinks: true,
    linkPinning: false,
    gridSize: 10,
    drawGrid: true,
    background: {
        color: '#e6ffff',
        opacity: 0.3
    },
    defaultLink: () => new joint.shapes.standard.Link(),
    highlighting: {
        'default': {
            name: 'stroke',
            options: {
                padding: 20
            }
        }
    },
    validateConnection: function (cellViewS, magnetS, cellViewT, magnetT, end, linkView) {
        if (magnetS && magnetS.getAttribute('port-group') === 'in') return false;
        if (cellViewS === cellViewT) return false;
        return magnetT && magnetT.getAttribute('port-group') === 'in';
    },
    validateMagnet: function (cellView, magnet) {
        return magnet.getAttribute('magnet') !== 'passive';
    },
    markAvailable: true,
});

var connect = function (source, sourcePort, target, targetPort) {

    var link = new joint.dia.Link({
        source: {
            id: source.id,
            port: sourcePort
        },
        target: {
            id: target.id,
            port: targetPort
        }
    });

    link.addTo(graph).reparent();
};

var previousCellView = null;

paper.on('link:mouseleave', function (linkView) {
    if (linkView.targetView !== null) {
        var nextElement = linkView.targetView;
        var prevElement = linkView.sourceView;
        if (prevElement.model.attributes.type == 'standard.Polygon') {
            nextElement.model.attributes.attrs.bodyText.anterior = linkView.sourceMagnet.nextSibling.textContent;
        }
    }
});

paper.on('element:pointerdown',
    function (elementView, evt, x, y) {
        elementView.highlight();

        if (elementView != previousCellView && previousCellView != null) {
            previousCellView.unhighlight();
        }
        previousCellView = elementView;

        $(document).keydown(function (event) {
            // Verifica se a tecla pressionada é "Backspace" ou "Delete"
            if (event.key === "Backspace" || event.key === "Delete") {
                elementView.model.remove();
            }
        });
    }
);

paper.on('element:pointerdblclick',
    async function (elementView, evt, x, y) {

        var cell = elementView.model;
        var sourceElement = null; // Declare a variável fora do escopo do loop para que ela seja acessível fora dele

        if (cell.isElement()) {
            var connectedLinks = graph.getConnectedLinks(cell, { inbound: true, outbound: true });
            connectedLinks.forEach(function (link) {
                if (link.get('target').id === cell.id) {
                    sourceElement = graph.getCell(link.get('source').id);
                }
            });
        }

        if (sourceElement && sourceElement.isElement() &&
            sourceElement.attributes.attrs.hasOwnProperty('bodyText') &&
            sourceElement.attributes.attrs.bodyText.hasOwnProperty('opcao') &&
            sourceElement.attributes.attrs.bodyText.hasOwnProperty('valor')) {
            elementView.model.attributes.attrs.bodyText.previousElement = {
                opcao: sourceElement.attributes.attrs.bodyText.opcao,
                valor: sourceElement.attributes.attrs.bodyText.valor,
                prevId: sourceElement.id
            };
            console.log(elementView);
            elementView.update();
        }

        var type = elementView.model.attributes.attrs.label.text;
        // Exibe um Sweet Alert com um campo de texto personalizado
        if (type == "Envia") {
            Swal.fire({
                title: "Digite a mensagem que você irá enviar",
                html: `<textarea class="form-control" id="chatBotDescription"></textarea>`,
                showCancelButton: true,
                confirmButtonText: 'Inserir',
                didOpen: () => {
                    document.getElementById('chatBotDescription').value = elementView.model.attributes.attrs.bodyText.description
                },
                preConfirm: () => {
                    const description = document.getElementById('chatBotDescription').value;
                    // Verifica se o valor é válido
                    if (!description) {
                        Swal.showValidationMessage('Por favor, insira uma mensagem!');
                    }
                    return description;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Obtém o valor do texto digitado
                    var inputValue = result.value;

                    // Verifica o tamanho do texto digitado
                    if (inputValue.length > 17) {
                        // Se for maior que 20 caracteres, trunca o texto
                        var shortText = inputValue.substring(0, 17) + "...";
                        // Define o texto truncado como descrição do elemento
                        elementView.model.attributes.attrs.bodyText.text = shortText;
                    } else {
                        // Caso contrário, mantém o texto original
                        elementView.model.attributes.attrs.bodyText.text = inputValue;
                    }
                    elementView.model.attributes.attrs.bodyText.description = inputValue;
                    elementView.model.attributes.attrs.bodyText.type = "envia";

                    // Atualiza a visualização do elemento
                    elementView.update();
                }
            });
        }
        if (type == "Recebe") {
            Swal.fire({
                title: "Mensagem recebida:",
                html: `
                    <select id="opcao" class="form-control mb-3">
                    <option value="igual">Igual a</option>
                    <option value="diferente">Diferente de</option>
                    <option value="contem">Contém</option>
                    <option value="naoContem">Não contém</option>
                    </select>
                    <input id="texto" class="form-control" placeholder="Digite o valor...">
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    document.getElementById('opcao').value = elementView.model.attributes.attrs.bodyText.opcao
                    document.getElementById('texto').value = elementView.model.attributes.attrs.bodyText.valor
                },
                preConfirm: () => {
                    const opcao = document.getElementById('opcao').value;
                    const texto = document.getElementById('texto').value;
                    if (!opcao || !texto) {
                        Swal.showValidationMessage('Por favor, preencha todos os campos.');
                    } else {
                        return { opcao: opcao, texto: texto };
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Se confirmado, exibe os valores no console
                    elementView.model.attributes.attrs.bodyText.opcao = result.value.opcao;
                    elementView.model.attributes.attrs.bodyText.valor = result.value.texto;
                    elementView.model.attributes.attrs.bodyText.type = "recebe";
                    elementView.update();
                }
            }
            );
        }
        if (type == "Arquivo" || type == "Audio" || type == "Foto") {
            Swal.fire({
                title: "Enviar " + type + ":",
                html: `
                <div class="mb-3">
                    <input id="fileSended" class="form-control" type="file" id="formFileMultiple">
                </div>`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: async () => {
                    const file = document.getElementById('fileSended').value;
                    if (!file) {
                        Swal.showValidationMessage('Por favor, envie um arquivo.');
                    } else {
                        var objChatBot = new ChatBot();
                        const link = await objChatBot.uploadToAWS('fileSended');

                        Swal.fire({
                            title: "Arquivo recebido",
                        });

                        return { link: link };
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    elementView.model.attributes.attrs.bodyText.arquivo = result.value.link;
                    elementView.update();
                }
            })
        }
        if (type == "Agendar") {

            var selectEvento = document.createElement("select");
            selectEvento.id = "eventosChatbot";
            selectEvento.classList.add('form-control');
            
            $.ajax({
                url: '/eventos/listagem',
                type: 'POST',
                success: function (data) {
    
                    var objData = JSON.parse(data)
    
                    var eventos = objData.data;

                    var option = document.createElement("option");
                    option.value = "";
                    option.innerHTML = "Selecione o evento...";
                    selectEvento.appendChild(option);

                    eventos.forEach(element => {
                        var option = document.createElement("option");
                        option.value = element.id;
                        option.innerHTML = element.nome;

                        selectEvento.appendChild(option);
                    });
                }
            })

            Swal.fire({
                title: "Selecione o evento a ser agendado",
                html: selectEvento,
                showCancelButton: true,
                confirmButtonText: 'Inserir',
                didOpen: () => {
                    document.getElementById('eventosChatbot').value = elementView.model.attributes.attrs.bodyText.evento
                },
                preConfirm: () => {
                    const evento = document.getElementById('eventosChatbot').value;
                    // Verifica se o valor é válido
                    if (!evento || evento == "") {
                        Swal.showValidationMessage('Por favor, selecione um evento!');
                    }
                    return evento;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Obtém o valor do texto digitado
                    var inputValue = result.value;
                    elementView.model.attributes.attrs.bodyText.evento = inputValue;
                    // Atualiza a visualização do elemento
                    elementView.update();
                }
            });
        }
        if (type == "Transferir") {
            var selectCargos = document.createElement("select");
            selectCargos.id = "cargosChatbot";
            selectCargos.classList.add('form-control');
            selectCargos.classList.add('mb-3');

            $.ajax({
                url: '/cargos/listagem',
                type: 'POST',
                success: function (data) {
                    var objData = JSON.parse(data)
                    var cargos = objData.data;

                    var option = document.createElement("option");
                    option.value = "";
                    option.innerHTML = "Transferir para qualquer funcionario do cargo...";
                    selectCargos.appendChild(option);

                    cargos.forEach(element => {
                        var option = document.createElement("option");
                        option.value = element.id;
                        option.innerHTML = element.nome;
                        selectCargos.appendChild(option);
                    });
                }
            })

            var selectFuncionario = document.createElement("select");
            selectFuncionario.id = "funcionariosChatbot";
            selectFuncionario.classList.add('form-control');
            
            $.ajax({
                url: '/funcionarios/listagem',
                data: {
                    onlyActive: true
                },
                type: 'POST',
                success: function (data) {
                    var objData = JSON.parse(data)
                    var funcionarios = objData.data;

                    var option = document.createElement("option");
                    option.value = "";
                    option.innerHTML = "Transferir para o funcionario...";
                    selectFuncionario.appendChild(option);

                    funcionarios.forEach(element => {
                        var option = document.createElement("option");
                        option.value = element.id;
                        option.innerHTML = element.nome;
                        selectFuncionario.appendChild(option);
                    });
                }
            })

            var div = document.createElement('div');
            div.appendChild(selectCargos);
            div.appendChild(selectFuncionario);

            Swal.fire({
                title: "Transfira o atendimento",
                html: div,
                showCancelButton: true,
                confirmButtonText: 'Inserir',
                didOpen: () => {
                    document.getElementById('cargosChatbot').value = elementView.model.attributes.attrs.bodyText.evento
                },
                preConfirm: () => {
                    const cargo = document.getElementById('cargosChatbot').value;
                    const funcinario = document.getElementById("funcionariosChatbot").value

                    // Verifica se o valor é válido
                    if ((!cargo && cargo == "") || (!funcinario && funcinario == "")) {
                        Swal.showValidationMessage('Por favor, transfira o atendimento para um funcinario ou um setor!');
                    }

                    return {cargo: cargo, funcinario: funcinario};                    
                    
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Obtém o valor do texto digitado
                    elementView.model.attributes.attrs.bodyText.setor = result.cargo;
                    elementView.model.attributes.attrs.bodyText.funcionario = result.funcinario;

                    // Atualiza a visualização do elemento
                    elementView.update();
                }
            });
        }
        if (type == "Salvar") {
            var selectSalvar = document.createElement("select");
            selectSalvar.id = "selectSalvarChatbot";
            selectSalvar.classList.add("form-control");

            var option = document.createElement("option");
            option.value = "";
            option.innerHTML = "Selecione o campo a ser salvo...";

            var arrOpcoes = ['nome', 'email', 'telefone', 'empresa', 'cargo'];

            arrOpcoes.forEach(element => {
                var option = document.createElement("option");
                option.value = element;
                option.innerHTML = element.charAt(0).toUpperCase() + element.slice(1);

                selectSalvar.appendChild(option);
            });
            
            Swal.fire({
                title: "Selecione o campo a ser salvo",
                html: selectSalvar,
                showCancelButton: true,
                confirmButtonText: 'Inserir',
                didOpen: () => {
                    document.getElementById('eventosChatbot').value = elementView.model.attributes.attrs.bodyText.evento
                },
                preConfirm: () => {
                    const evento = document.getElementById('eventosChatbot').value;                    // Verifica se o valor é válido
                    if (!evento || evento == "") {
                        Swal.showValidationMessage('Por favor, selecione um evento!');
                    }
                    return evento;
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Obtém o valor do texto digitado
                    var inputValue = result.value;
                    elementView.model.attributes.attrs.bodyText.campo = inputValue;
                    // Atualiza a visualização do elemento
                    elementView.update();
                }
            });
        }
    }
);

paper.on('blank:pointerdown',
    function (evt, x, y) {
        if (previousCellView != null) {
            previousCellView.unhighlight();
        }
    }
);



// Adicionar botão de mais zoom
$('#zoom-in').on('click', function () {
    paper.scale(paper.scale().sx + 0.1, paper.scale().sy + 0.1);
});

// Adicionar botão de menos zoom
$('#zoom-out').on('click', function () {
    paper.scale(paper.scale().sx - 0.1, paper.scale().sy - 0.1);
});

// #region portas
var portsIn = {
    position: {
        name: 'left'
    },
    attrs: {
        portBody: {
            magnet: true,
            r: 10,
            fill: '#023047',
            stroke: '#023047'
        }
    },
    label: {
        position: {
            name: 'left',
            args: { y: 6 }
        },
        markup: [{
            tagName: 'text',
            selector: 'label',
            className: 'label-text'
        }]
    },
    markup: [{
        tagName: 'circle',
        selector: 'portBody'
    }]
};

var portsOut = {
    position: {
        name: 'right'
    },
    attrs: {
        portBody: {
            magnet: true,
            r: 10,
            fill: '#E6A502',
            stroke: '#023047'
        }
    },
    label: {
        position: {
            name: 'right',
            args: { y: 6 }
        },
        markup: [{
            tagName: 'text',
            selector: 'label',
            className: 'label-text'
        }]
    },
    markup: [{
        tagName: 'circle',
        selector: 'portBody'
    }]
};
// #endregion

//#region Basic
var draggableContainerBasic = new joint.dia.Graph;
var draggableBasic = new joint.dia.Paper({
    el: document.getElementById('draggableBasic'),
    gridSize: 1,
    width: 325,
    height: 210,
    model: draggableContainerBasic,
    interactive: false,
    background: {
        color: '#ecf8ec',
        opacity: 0.3
    },
});

// #region Inicio
var start = new joint.shapes.standard.InscribedImage({
    ports: {
        groups: {
            'out': portsOut
        }
    }
});
start.resize(50, 50);
start.position(45, 10)
start.attr('root/title', 'joint.shapes.standard.InscribedImage');
start.attr('label/text', 'Início');
start.attr('border/strokeWidth', 5);
start.attr('background/fill', 'lightgreen');
start.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/player-play.png');
start.addPorts([
    {
        group: 'out',
    }
]);
start.addTo(draggableContainerBasic);


// #endregion

// #region Termina

var end = new joint.shapes.standard.InscribedImage({
    ports: {
        groups: {
            'in': portsIn
        }
    }
});
end.resize(50, 50);
end.position(220, 10)
end.attr('root/title', 'joint.shapes.standard.InscribedImage');
end.attr('label/text', 'Fim');
end.attr('border/strokeWidth', 5);
end.attr('background/fill', 'red');
end.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/player-stop.png');
end.addPorts([
    {
        group: 'in',
    }
]);
end.addTo(draggableContainerBasic);

// #endregion

// #region Mensagem enviada
var msgEnviada = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
msgEnviada.resize(100, 80);
msgEnviada.position(20, 90);
msgEnviada.attr('root/title', 'Mensagem Enviada');
msgEnviada.attr('label/text', 'Envia');
msgEnviada.attr('background/fill', 'lightblue');
msgEnviada.attr('border/rx', 5);
msgEnviada.attr('bodyText/description', '');
msgEnviada.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/message-2.png');
msgEnviada.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
msgEnviada.addTo(draggableContainerBasic);

// #endregion

// #region msg recebida
var msgRecebida = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
msgRecebida.resize(100, 80);
msgRecebida.position(190, 90);
msgRecebida.attr('root/title', 'Mensagem Recebida');
msgRecebida.attr('label/text', 'Recebe');
msgRecebida.attr('background/fill', 'lightorange');
msgRecebida.attr('border/rx', 5);
msgRecebida.attr('bodyText/description', '');
msgRecebida.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/message.png');
msgRecebida.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
msgRecebida.addTo(draggableContainerBasic);
// #endregion

// #endregion
draggableBasic.on('cell:pointerdown', function (cellView, e, x, y) {
    $('body').append('<div id="flyPaper" style="position:relative;opacity:0.4;pointer-event:none;"></div>');
    var flyGraph = new joint.dia.Graph,
        flyPaper = new joint.dia.Paper({
            el: $('#flyPaper'),
            model: flyGraph,
            height: 100,
            width: 110,
            interactive: false
        }),
        flyShape = cellView.model.clone(),
        pos = cellView.model.position(),
        offset = {
            x: x - pos.x,
            y: y - pos.y
        };

    flyShape.position(15, 10);
    flyShape.prop = 1;
    flyGraph.addCell(flyShape);
    $("#flyPaper").offset({
        left: e.pageX - offset.x,
        top: e.pageY - offset.y
    });
    $('body').on('mousemove.fly', function (e) {
        $("#flyPaper").offset({
            left: e.pageX - offset.x,
            top: e.pageY - offset.y
        });
    });
    $('body').on('mouseup.fly', function (e) {
        var x = e.pageX,
            y = e.pageY,
            target = paper.$el.offset();

        // Dropped over paper ?
        if (x > target.left && x < target.left + paper.$el.width() && y > target.top && y < target.top + paper.$el.height()) {
            var s = flyShape.clone();
            s.position(x - target.left - offset.x, y - target.top - offset.y);
            graph.addCell(s);
        }
        $('body').off('mousemove.fly').off('mouseup.fly');
        flyShape.remove();
        $('#flyPaper').remove();
    });
});
// #endregion


// #region Anexo
var draggableContainerAnexo = new joint.dia.Graph;
var draggableAnexo = new joint.dia.Paper({
    el: document.getElementById('draggableAnexo'),
    gridSize: 1,
    width: 325,
    height: 210,
    model: draggableContainerAnexo,
    interactive: false,
    background: {
        color: '#ecf8ec',
        opacity: 0.3
    },
});


// #region Arquivo
var arquivo = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
arquivo.resize(90, 70);
arquivo.position(20, 5);
arquivo.attr('root/title', 'Arquivo Enviado');
arquivo.attr('label/text', 'Arquivo');
arquivo.attr('background/fill', 'lightblue');
arquivo.attr('border/rx', 5);
arquivo.attr('bodyText/description', '');
arquivo.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/file.png');
arquivo.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
arquivo.addTo(draggableContainerAnexo);

// #endregion

// #region Foto
var foto = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
foto.resize(90, 70);
foto.position(190, 5);
foto.attr('root/title', 'Foto enviada');
foto.attr('label/text', 'Foto');
foto.attr('background/fill', 'lightorange');
foto.attr('border/rx', 5);
foto.attr('bodyText/description', '');
foto.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/photo.png');
foto.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
foto.addTo(draggableContainerAnexo);
// #endregion

// #region Audio
var audio = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
audio.resize(90, 70);
audio.position(20, 100);
audio.attr('root/title', 'Audio enviado');
audio.attr('label/text', 'Audio');
audio.attr('background/fill', 'lightorange');
audio.attr('border/rx', 5);
audio.attr('bodyText/description', '');
audio.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/microphone.png');
audio.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
audio.addTo(draggableContainerAnexo);
// #endregion

// #region Local
var local = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
local.resize(90, 70);
local.position(190, 100);
local.attr('root/title', 'Localização enviada');
local.attr('label/text', 'Localização');
local.attr('background/fill', 'lightorange');
local.attr('border/rx', 5);
local.attr('bodyText/description', '');
local.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/current-location.png');
local.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
local.addTo(draggableContainerAnexo);
// #endregion

// #endregion
draggableAnexo.on('cell:pointerdown', function (cellView, e, x, y) {
    $('body').append('<div id="flyPaper" style="position:relative;opacity:0.4;pointer-event:none;"></div>');
    var flyGraph = new joint.dia.Graph,
        flyPaper = new joint.dia.Paper({
            el: $('#flyPaper'),
            model: flyGraph,
            height: 100,
            width: 110,
            interactive: false
        }),
        flyShape = cellView.model.clone(),
        pos = cellView.model.position(),
        offset = {
            x: x - pos.x,
            y: y - pos.y
        };

    flyShape.position(15, 10);
    flyShape.prop = 1;
    flyGraph.addCell(flyShape);
    $("#flyPaper").offset({
        left: e.pageX - offset.x,
        top: e.pageY - offset.y
    });
    $('body').on('mousemove.fly', function (e) {
        $("#flyPaper").offset({
            left: e.pageX - offset.x,
            top: e.pageY - offset.y
        });
    });
    $('body').on('mouseup.fly', function (e) {
        var x = e.pageX,
            y = e.pageY,
            target = paper.$el.offset();

        // Dropped over paper ?
        if (x > target.left && x < target.left + paper.$el.width() && y > target.top && y < target.top + paper.$el.height()) {
            var s = flyShape.clone();
            s.position(x - target.left - offset.x, y - target.top - offset.y);
            graph.addCell(s);
        }
        $('body').off('mousemove.fly').off('mouseup.fly');
        flyShape.remove();
        $('#flyPaper').remove();
    });
});
// #endregion

// #endregion

// #region Acoes
var draggableContainerAcoes = new joint.dia.Graph;
var draggableAcoes = new joint.dia.Paper({
    el: document.getElementById('draggableAcoes'),
    gridSize: 1,
    width: 325,
    height: 210,
    model: draggableContainerAcoes,
    interactive: false,
    background: {
        color: '#ecf8ec',
        opacity: 0.3
    },
});


// #region Agendar
var agendar = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
agendar.resize(90, 70);
agendar.position(20, 5);
agendar.attr('root/title', 'Agendar');
agendar.attr('label/text', 'Agendar');
agendar.attr('background/fill', 'lightblue');
agendar.attr('border/rx', 5);
agendar.attr('bodyText/evento', '');
agendar.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/calendar-clock.png');
agendar.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
agendar.addTo(draggableContainerAcoes);

// #endregion

// #region Transferir
var transferir = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
transferir.resize(90, 70);
transferir.position(190, 5);
transferir.attr('root/title', 'Transferir');
transferir.attr('label/text', 'Transferir');
transferir.attr('background/fill', 'lightorange');
transferir.attr('border/rx', 5);
transferir.attr('bodyText/funcionario', '');
transferir.attr('bodyText/setor', '');
transferir.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/headset.png');
transferir.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
transferir.addTo(draggableContainerAcoes);
// #endregion

// #region Salvar dados
var salvar = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
salvar.resize(90, 70);
salvar.position(20, 100);
salvar.attr('root/title', 'Salvar dados');
salvar.attr('label/text', 'Salvar');
salvar.attr('background/fill', 'lightorange');
salvar.attr('border/rx', 5);
salvar.attr('bodyText/campo', '');
salvar.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/device-floppy.png');
salvar.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
salvar.addTo(draggableContainerAcoes);
// #endregion

// #region Adicionar Tags
var tags = new joint.shapes.standard.Image({
    ports: {
        groups: {
            'in': portsIn,
            'out': portsOut
        }
    }
});
tags.resize(90, 70);
tags.position(190, 100);
tags.attr('root/title', 'Adicionar tag');
tags.attr('label/text', 'Adicionar tag');
tags.attr('background/fill', 'lightorange');
tags.attr('border/rx', 5);
tags.attr('bodyText/tags', '');
tags.attr('image/xlinkHref', '../../resources/images/ElementosJointJs/tags.png');
tags.addPorts([
    {
        group: 'in',
    },
    {
        group: 'out',
    }
]);
tags.addTo(draggableContainerAcoes);

draggableAcoes.on('cell:pointerdown', function (cellView, e, x, y) {
    $('body').append('<div id="flyPaper" style="position:relative;opacity:0.4;pointer-event:none;"></div>');
    var flyGraph = new joint.dia.Graph,
        flyPaper = new joint.dia.Paper({
            el: $('#flyPaper'),
            model: flyGraph,
            height: 100,
            width: 110,
            interactive: false
        }),
        flyShape = cellView.model.clone(),
        pos = cellView.model.position(),
        offset = {
            x: x - pos.x,
            y: y - pos.y
        };

    flyShape.position(15, 10);
    flyShape.prop = 1;
    flyGraph.addCell(flyShape);
    $("#flyPaper").offset({
        left: e.pageX - offset.x,
        top: e.pageY - offset.y
    });
    $('body').on('mousemove.fly', function (e) {
        $("#flyPaper").offset({
            left: e.pageX - offset.x,
            top: e.pageY - offset.y
        });
    });
    $('body').on('mouseup.fly', function (e) {
        var x = e.pageX,
            y = e.pageY,
            target = paper.$el.offset();

        // Dropped over paper ?
        if (x > target.left && x < target.left + paper.$el.width() && y > target.top && y < target.top + paper.$el.height()) {
            var s = flyShape.clone();
            s.position(x - target.left - offset.x, y - target.top - offset.y);
            graph.addCell(s);
        }
        $('body').off('mousemove.fly').off('mouseup.fly');
        flyShape.remove();
        $('#flyPaper').remove();
    });
});
// #endregion