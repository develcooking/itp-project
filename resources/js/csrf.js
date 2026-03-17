/**
 * CSRF Protection for AJAX requests
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
        (function($) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        })(jQuery);
    }
});
