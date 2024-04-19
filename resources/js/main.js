class Main {
    // Função para enviar dados para o servidor via AJAX usando jQuery
    validar(campo, formulario = '') {
        // Verifica se os campos estão preenchidos
        if (campo === '') {

            
            // Exibe alerta

            // Adiciona borda vermelha temporária
            var form = document.querySelector(formulario);
            var campos = form.querySelectorAll('input');

            campos.forEach(function (campo) {
                if (campo.value === '') {
                    campo.classList.add('is-invalid');
                }
            });
            $('#preenchaCampos').show();

            // Remove a borda vermelha após 5 segundos
            setTimeout(function () {
                campos.forEach(function (campo) {
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

    confirmWithInputAndCallback(confirmText, inputText, confirmCallback) {
        Swal.fire({
            title: confirmText,
            text: inputText,
            input: "text",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, excluir!",
            cancelButtonText: "Cancelar",
            inputValidator: (value) => {
                // Validate input value
                if (value !== "excluir permanentemente") {
                    return 'Você precisa digitar "excluir permanentemente"!';
                } else {                    
                    confirmCallback();
                    Swal.fire({
                        title: "Registro excluído com sucesso!",
                        text: "Esse registro foi excluído para todo o sempre.",
                        icon: "success"
                    }).then((result) => {
                        location.reload();
                    });
                }
            }
        })
    }

}
