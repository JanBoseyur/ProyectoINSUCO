document.addEventListener('click', event => {
    const boton = event.target.closest('[data-description-toggle]');
    if (!boton) return;

    const descripcion = boton.closest('.ticketDescription');
    if (!descripcion) return;

    const expandida = descripcion.classList.toggle('isExpanded');
    boton.setAttribute('aria-expanded', String(expandida));
    boton.textContent = expandida ? 'Ver menos' : 'Ver más';
    boton.setAttribute(
        'aria-label',
        `${expandida ? 'Contraer' : 'Expandir'} descripción del ticket ${boton.dataset.ticketId}`
    );
});
