// E-Learning Platform - jQuery (PDF: Javascript et la bibliothèque jQuery)
$(function() {
    // Focus search input on load when present
    $(".search-input").first().focus();
    // Smooth scroll for in-page links
    $('a[href^="#"]').on("click", function(e) {
        var target = $(this.getAttribute("href"));
        if (target.length) { e.preventDefault(); $("html, body").animate({ scrollTop: target.offset().top }, 400); }
    });
});
