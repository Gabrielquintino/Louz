class Dashboard {

	constructor() {
		var select = $('#formDashboard #data').val()
		this.list(select);
	}

	async list(strData, booDestroy = false) {

        await $.ajax({
            url: '/atendimento/noPeriodo',
            data: {
                'data': strData
            },
            type: 'POST',
            async: false,
            success: function (data) {
                var objData = JSON.parse(data)

                $('#dashboard #totalAtendimentos').html(objData.data[0].qt_total)
                $('#dashboard #clienteAtendimento').html(objData.data[0].qt_andamento)
                $('#dashboard #clienteEspera').html(objData.data[0].qt_espera)
                $('#dashboard #notaAtendimento').html(objData.data[0].avg_avaliacao)
                $('#dashboard #tma').html(objData.data[0].avg_media)
                $('#dashboard #tempoEspera').html(objData.data[0].avg_espera)
                $('#dashboard #atendimentoFinalizado').html(objData.data[0].qt_encerrado)
                $('#dashboard #totalVendas').html(objData.data[0].qt_vendas)
                $('#dashboard #faturamento').html(objData.data[0].faturamento)
                $('#dashboard #ticketMedio').html(objData.data[0].ticket_medio)
                $('#dashboard #melhorVendedor').html(objData.data[0].melhor_funcionario)
                var divFuncionario = document.getElementById('melhorVendedor').parentElement;
                divFuncionario.title = objData.data[0].melhor_funcionario


                $('#dashboard #produtoMaisVendido').html(objData.data[0].produto_mais_vendido)
                var divProduto = document.getElementById('produtoMaisVendido').parentElement;
                divProduto.title = objData.data[0].produto_mais_vendido

                var floConversao = 0;
                if (objData.data[0].qt_vendas != 0 && objData.data[0].qt_encerrado != 0 ) {
                    floConversao = ( (objData.data[0].qt_vendas * 100) / objData.data[0].qt_encerrado ).toFixed(2);                    
                }

                $('#dashboard #conversao').html(floConversao + "%");
            }
        })

        await $.ajax({
            url: '/vendas/noPeriodo',
            data: {
                'data': strData
            },
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
                            label: 'Vendas por Dia',
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

    async loadSalesData() {
        try {
            // Substitua pela sua URL de API no backend que retorna os dados de vendas agrupados por dia
            const apiUrl = '/api/vendas-por-dia';

            const response = await fetch(apiUrl);
            const data = await response.json();



        } catch (error) {
            console.error('Erro ao carregar dados:', error);
        }
    }

}