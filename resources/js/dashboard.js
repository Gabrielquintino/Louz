class Dashboard {

	constructor() {
		var select = $('#formDashboard #data').val()
        this.verificaPassos();
	}

	listar(strData, booDestroy = false) {

        $('#inicio').hide();
        $('#dashboard').show();

        $.ajax({
            url: '/atendimento/noPeriodo',
            data: {
                'data': strData
            },
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)

                var arr = []

                $.each(objData.data[0], function(index, value) {
                    if (value == null) {
                        arr[index] = 0;
                    } else {
                        arr[index] = value;
                    }
                })
                
                console.log(arr)

                $('#dashboard #totalAtendimentos').html(arr['qt_total'])
                $('#dashboard #clienteAtendimento').html(arr['qt_andamento'])
                $('#dashboard #clienteEspera').html(arr['qt_espera'])
                $('#dashboard #notaAtendimento').html(arr['avg_avaliacao'])
                $('#dashboard #tma').html(arr['avg_media'])
                $('#dashboard #tempoEspera').html(arr['avg_espera'])
                $('#dashboard #atendimentoFinalizado').html(arr['qt_encerrado'])
                $('#dashboard #totalVendas').html(arr['qt_vendas'])
                $('#dashboard #faturamento').html(arr['faturamento'])
                $('#dashboard #ticketMedio').html(arr['ticket_medio'])
                $('#dashboard #melhorVendedor').html(arr['melhor_funcionario'])
                var divFuncionario = document.getElementById('melhorVendedor').parentElement;
                divFuncionario.title = arr['melhor_funcionario']


                $('#dashboard #produtoMaisVendido').html(arr['produto_mais_vendido'])
                var divProduto = document.getElementById('produtoMaisVendido').parentElement;
                divProduto.title = arr['produto_mais_vendido']

                var floConversao = 0;
                if (arr['qt_vendas'] != 0 && arr['qt_encerrado'] != 0 ) {
                    floConversao = ( (arr['qt_vendas'] * 100) / arr['qt_encerrado'] ).toFixed(2);
                }

                $('#dashboard #conversao').html(floConversao + "%");

                return;
            }
        })

        $.ajax({
            url: '/vendas/noPeriodo',
            data: {
                'data': strData
            },
            async: false,
            type: 'POST',
            success: function (data) {
                var objData = JSON.parse(data);
                console.log(objData);
        
                if (objData.data.length === 0) {
                    // Caso não haja dados, exibir uma mensagem ou tratar conforme necessário
                    console.log('Nenhum dado disponível para exibir.');
                    return;
                }
        
                // Preparar arrays para labels (dias de venda) e dados (total de vendas)
                const labels = [];
                const dataPoints = [];
        
                objData.data.forEach(item => {
                    // Formatar a data usando Moment.js para o formato desejado
                    const formattedDate = moment(item.venda_dia).format('DD/MM/YY');
                    labels.push(formattedDate); // Adiciona o dia de venda formatado ao array de labels
                    dataPoints.push(parseFloat(item.total_vendas)); // Converte total_vendas para float e adiciona ao array de dados
                });
        
                // Verificar se há um gráfico existente e destruí-lo se necessário
                var ctx = document.getElementById('myChart').getContext('2d');
                if (booDestroy) {
                    window.myChart.destroy();
                }
        
                // Configurações do novo gráfico
                window.myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels, // Labels com os dias de venda formatados
                        datasets: [{
                            label: 'Vendido no dia',
                            data: dataPoints, // Dados com os totais de vendas
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                // Define o máximo do eixo Y baseado nos dados de vendas
                                suggestedMax: Math.max(...dataPoints) + 10
                            }
                        }
                    }
                });   
            },
            error: function (error) {
                console.error('Erro ao obter dados:', error);
                // Tratar o erro conforme necessário
            }
        });   
	}

    verificaPassos() {
        
        var dash = this;
        
        $.ajax({
            url: '/verificarEtapas',
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data);   
                var divconecta = document.getElementById('conecta');
                var divchatbot = document.getElementById('chatbot');
                var divEtapas = document.getElementById('etapas');
                var divFuncionario = document.getElementById('funcionario');
                var divEvento = document.getElementById('evento');
                var divProduto = document.getElementById('produto');

                if (objData[0].chatbot > 0 &&
                    objData[0].etapas > 0 && 
                    objData[0].eventos > 0 && 
                    objData[0].funcionarios > 0 && 
                    objData[0].instancia > 0 && 
                    objData[0].produtos > 0
                ) {
                    dash.listar($('#formDashboard #data').val());
                } else {
                    if (objData[0].chatbot > 0) {
                        var icon = divchatbot.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divchatbot.getElementsByTagName('a');
                        a[0].remove();
                    }
                    if (objData[0].etapas > 0) {
                        var icon = divEtapas.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divEtapas.getElementsByTagName('a');
                        a[0].remove();
                    }
                    if (objData[0].eventos > 0) {
                        var icon = divEvento.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divEvento.getElementsByTagName('a');
                        a[0].remove();
                    }
                    if (objData[0].funcionarios > 0) {
                        var icon = divFuncionario.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divFuncionario.getElementsByTagName('a');
                        a[0].remove();
                    }
                    if (objData[0].instancia > 0) {
                        var icon = divconecta.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divconecta.getElementsByTagName('a');
                        a[0].remove();
                    }
                    if (objData[0].produtos > 0) {
                        var icon = divProduto.getElementsByTagName('i');
                        icon[0].classList = "ti ti-square-check text-success";
                        var a = divProduto.getElementsByTagName('a');
                        a[0].remove();
                    }
    
                    $('#dashboard').hide();
                    $('#inicio').show();
                }

            }
        })
    }
}