
document.addEventListener('DOMContentLoaded', () => {

    const buscador = document.getElementById('buscadorTicket');

    if (!buscador) {
        console.error('No se encontró el buscadorTicket');
        return;
    }

    console.log('Buscador conectado correctamente');

    buscador.addEventListener('input', function () {
        console.log('Texto ingresado:', this.value);
    });

});