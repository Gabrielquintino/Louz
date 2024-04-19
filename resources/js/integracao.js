class Integracao {

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/listagemIntegracao',
            type: 'POST',
            success: function (data) {

                var objData = JSON.parse(data)

                var template = document.getElementById('listIntegracaoTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData);

                if (objData.data.length > 0) {
                    $('#NovaConexao').hide();                    
                } else {
                    $('#NovaConexao').show();
                }

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listIntegracao').innerHTML = rendered;

            }
        })
    }

    genereteQrCode() {

        $.ajax({
            url: '/getQrCode',
            type: 'POST',
            async: false,
            await: true,
            success: function (data) {
                var objData = JSON.parse(data)
                $('#insiraTelefone').hide();
                $('#gerandoQrCode').show();
                var img = document.getElementById('qrCodeImg');
                img.src = objData.data;
            },
            error: function () {
                Swal.fire({
                    title: "Ops!",
                    text: "Nao foi possivel gerar o QrCode. Tente novamente mais tarde!",
                    icon: "error"
                })
            }
        })


    }

    save() {
        $.ajax({
            url: '/getWppInstance',
            type: 'POST',
            async: false,
            await: true,
            success: function (data) {

                var objData = JSON.parse(data)

                if (objData.success) {
                    Swal.fire({
                        title: "Uhuuul!",
                        text: "Conectado com sucesso.",
                        icon: "success"
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Ops!",
                        text: "Parece que voce ainda nao conseguiu se conectar, tente novamente em alguns instantes.",
                        icon: "error"
                    })
                }
            },
            error: function () {
                Swal.fire({
                    title: "Ops!",
                    text: "Parece que voce ainda nao conseguiu se conectar, tente novamente em alguns instantes.",
                    icon: "error"
                })
            }
        })
    }

    delete(intId) { // vai desconectar a instancia


        function deletar(id) {
            $.ajax({
                url: '/deleteWppInstance',
                type: 'POST',
                async: false,
                await: true,
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
}