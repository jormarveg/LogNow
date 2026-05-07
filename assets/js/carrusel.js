$(document).ready(function () {
    const elemento = document.getElementById('carouselTendencias');
    const flechaIzquierda = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" tabindex="-1" aria-hidden="true"><path d="M15 4 7 12l8 8" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>';
    const flechaDerecha = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" tabindex="-1" aria-hidden="true"><path d="M9 4l8 8-8 8" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>';

    if (!elemento || typeof Carousel === 'undefined' || typeof Arrows === 'undefined') {
        return;
    }

    Carousel(elemento, {
        infinite: true,
        fill: false,
        dragFree: false,
        Autoplay: {
            timeout: 3500
        },
        Arrows: {
            prevTpl: flechaIzquierda,
            nextTpl: flechaDerecha
        }
    }, {
        Arrows
    }).init();
});
