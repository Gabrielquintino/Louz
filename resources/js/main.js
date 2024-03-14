class Main {
    // Função para enviar dados para o servidor via AJAX usando jQuery
    validar(campo, formulario = ''){    
        // Verifica se os campos estão preenchidos
        if (campo === '') {
            // Exibe alerta
            $('#preenchaCampos').show();
    
            // Adiciona borda vermelha temporária
            var campos = document.querySelectorAll( formulario + '.campo');
            campos.forEach(function(campo) {
                campo.style.border = '2px solid red';
            });
    
            // Remove a borda vermelha após 5 segundos
            setTimeout(function() {
                campos.forEach(function(campo) {
                    campo.style.border = '';
                });
            }, 5000);
            return false;
        } else {
            // Se os campos estiverem preenchidos, chama a função entrar()
            return true;
        }
    }
    entrar() {
        // Configuração da requisição
        $('#preenchaCampos').hide();

        var emailValido = this.validar(document.getElementById("email").value, "#login")
        var senhaValida = this.validar(document.getElementById("senha").value, "#login")

        if (emailValido && senhaValida) {
            $.ajax({
                url: '/entrar',
                type: 'POST',
                data: {
                    email: $('#email').val(),
                    senha: $('#senha').val()
                },
                success: function (data) {
                    var jsonData = JSON.parse(data);
                    // Processa a resposta do servidor
                    if (jsonData.success) {
                        Swal.fire({
                            title: "Sucesso!",
                            text: "Seja bem-vindo",
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
                    // Lida com erros
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
            });            
        }
    }
}
