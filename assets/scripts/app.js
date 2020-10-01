$(document).ready(function(){
    $('.js-netlab-slider-slick').slick({
        infinite: true,
        autoplay: true,
        autoplaySpeed: 5000,
        dots: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: true,
        // centerPadding: '60px',
        // variableWidth: true,
        fade: true,
        cssEase: 'linear',
    });
});