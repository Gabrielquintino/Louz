Handlebars.registerHelper('formatDate', (dateString) => {
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short',
    };

    const formattedDate = new Date(dateString).toLocaleString('pt-BR', options);

    // Removendo o sufixo de fuso horário
    const dateWithoutTimeZone = formattedDate.replace(/ [A-Z]{3}$/, '');

    return dateWithoutTimeZone;
});

// Helper para extrair o número de telefone
Handlebars.registerHelper('extractPhoneNumber', (filePath) => {
    const phoneNumber = filePath.match(/\d+/)[0];
    return phoneNumber;
});