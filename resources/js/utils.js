class Utils {
    validarNumeroTelefone(numero) {
        // Remova espaços em branco e caracteres especiais
        const numeroLimpo = numero.replace(/\D/g, '');

        // Verifique se o número possui apenas dígitos
        if (!/^\d+$/.test(numeroLimpo)) {
            return false;
        }

        // Verifique se o número atende ao comprimento mínimo e máximo desejado
        const comprimentoMinimo = 8; // ajuste conforme necessário
        const comprimentoMaximo = 15; // ajuste conforme necessário

        return numeroLimpo.length >= comprimentoMinimo && numeroLimpo.length <= comprimentoMaximo;
    }

    aplicarClasseInvalida(elementId, invalid) {
        const elemento = document.getElementById(elementId);
        if (elemento) {
            if (invalid) {
                elemento.classList.add('is-invalid');
            } else {
                elemento.classList.remove('is-invalid');
            }

            elemento.addEventListener('input', function () {
                this.classList.remove('is-invalid');
            });
        }
    }

}