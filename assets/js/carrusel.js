// carrusel con biblioteca FancyApps
$(document).ready(function () {
    const elementos = document.querySelectorAll('.carousel-juegos-home');

    if (!elementos.length || typeof Carousel === 'undefined' || typeof Arrows === 'undefined') {
        return;
    }

    elementos.forEach(function (elemento) {
        Carousel(elemento, {
            infinite: true,
            fill: false,
            dragFree: false,
            Autoplay: {
                timeout: 3500
            }
        }, {
            Arrows
        }).init();
    });
});
