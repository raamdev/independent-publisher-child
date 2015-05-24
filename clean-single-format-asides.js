jQuery( function() {

    $window = jQuery( window );

    function pe_check_scroll_position() {

        if ( $window.scrollTop() == 0 ) {
            jQuery( 'body.single-format-aside .post-author-bottom' ).css('opacity', '0');
        } else {
            jQuery( 'body.single-format-aside .post-author-bottom' ).css('opacity', '1');
        }
    }

    pe_check_scroll_position();

    $window.scroll( pe_check_scroll_position );

});
