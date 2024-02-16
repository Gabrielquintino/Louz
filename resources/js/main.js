class ClonePage {

    // Método da classe
    // script.js

    // Função para enviar dados para o servidor via AJAX usando jQuery
    import() {

        // Configuração da requisição
        $.ajax({
            url: '/import',
            type: 'POST',
            data: {
                name: $('#name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                url: $('#url').val()
            },
            success: function (data) {
                // Processa a resposta do servidor
                Swal.fire({
                    title: "Sucesso!",
                    text: "Obrigado por se cadastrar! Em breve, você receberá um e-mail ou uma mensagem no WhatsApp quando tudo estiver pronto. Fique de olho na sua caixa de entrada!",
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        location.reload();
                    }
                });
            },
            error: function (error) {
                // Lida com erros
                console.error('Erro ao salvar o usuário:', error);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Alguma coisa deu errado!",
                    footer: 'Clique <a target="blank" href="https://web.whatsapp.com/send?phone=48988267601">aqui</a> para entrar em contato com o suporte.'
                });
            }
        });
    }
}
