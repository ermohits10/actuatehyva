define(['jquery', 'owlcarousel'], function($) {
    $(document).ready(function() {
        $('.owl-carousel').owlCarousel({
	        loop: true,
	        margin: 10,
	        nav: true,
	        navText: [
		        "<",
		        ">"
	        ],
	        autoplay: true,
	        autoplayHoverPause: true,
	        responsive: {
	            0: {
	              items: 1
	            },
	            400: {
	              items: 3
	            },
	            600: {
	              items: 6
	            }
	        }
	    });
    });
});
