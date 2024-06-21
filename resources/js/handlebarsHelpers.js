Handlebars.registerHelper('formatDate', (dateString) => {
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZoneName: 'short',
    };

    // Formatar a data como string
    const formattedDate = new Date(dateString).toLocaleString('pt-BR', options);

    // Remover o sufixo de fuso horário
    const dateWithoutTimeZone = formattedDate.replace(/ [A-Z]{3}$/, '');

    // Separar a parte da data e da hora
    const [datePart, timePart] = dateWithoutTimeZone.split(' ');

    // Separar dia, mês e ano
    const [day, month, year] = datePart.split('/');

    // Manter apenas os dois últimos dígitos do ano
    const shortYear = year.slice(-3);

    // Reformatar a data com o ano encurtado
    const shortYearDate = `${day}/${month}/${shortYear} ${timePart}`;

    return shortYearDate;
});

Handlebars.registerHelper('formatTimeStamp', (dateString) => {
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZoneName: 'short',
    };

    // Formatar a data como string
    const formattedDate = new Date(dateString).toLocaleString('pt-BR', options);

    // Remover o sufixo de fuso horário
    const dateWithoutTimeZone = formattedDate.replace(/ [A-Z]{3}$/, '');

    // Separar a parte da data e da hora
    const [datePart, timePart] = dateWithoutTimeZone.split(' ');

    // Separar dia, mês e ano
    const [day, month, year] = datePart.split('/');

    // Reformatar a data com o ano encurtado
    const shortYearDate = `${day}/${month}/${year} ${timePart}`;

    return shortYearDate;
});

// Helper para extrair o número de telefone
Handlebars.registerHelper('extractPhoneNumber', (filePath) => {
    const phoneNumber = filePath.match(/\d+/)[0];
    return phoneNumber;
});

Handlebars.registerHelper("setVar", function(varName, varValue, options) {
  options.data.root[varName] = varValue;
});