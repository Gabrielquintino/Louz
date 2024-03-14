class Integracao {

    constructor() {
        this.list();
    }

    list() {

        $.ajax({
            url: '/listagemIntegracao',
            type: 'POST',
            async: true,
            success: function (data) {

                var objData1 = JSON.parse(data)

                var template = document.getElementById('listIntegracaoTemplate').innerHTML;

                //Compile the template
                var compiled_template = Handlebars.compile(template);

                //Render the data into the template
                var rendered = compiled_template(objData1);

                console.log(rendered)

                //Overwrite the contents of #target with the renderer HTML
                document.getElementById('listIntegracao').innerHTML = rendered;




            }
        })
    }
}