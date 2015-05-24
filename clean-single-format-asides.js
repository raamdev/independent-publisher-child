jQuery( function() {

    $window = jQuery( window );

    function pe_check_scroll_position() {

        if ( $window.scrollTop() == 0 ) {
            jQuery( 'body.single-format-aside .entry-header' ).css('opacity', '0');
            jQuery( 'body.single-format-aside .post-author-bottom' ).css('opacity', '0');
            jQuery( 'body.single-format-aside .entry-meta' ).css('opacity', '0');
            jQuery( 'body.single-format-aside .comments-area' ).css('opacity', '0');
            jQuery( 'body.single-format-aside #further-reading' ).css('opacity', '0');
            jQuery( 'body.single-format-aside #taglist' ).css('opacity', '0');
            jQuery( 'body.single-format-aside .widget-area' ).css('opacity', '0');
            jQuery( 'body.single-format-aside .site-footer' ).css('opacity', '0');
            jQuery( 'body.single-format-aside #respond' ).css('opacity', '0');
        } else {
            jQuery( 'body.single-format-aside .entry-header' ).css('opacity', '1');
            jQuery( 'body.single-format-aside .post-author-bottom' ).css('opacity', '1');
            jQuery( 'body.single-format-aside .entry-meta' ).css('opacity', '1');
            jQuery( 'body.single-format-aside .comments-area' ).css('opacity', '1');
            jQuery( 'body.single-format-aside #further-reading' ).css('opacity', '1');
            jQuery( 'body.single-format-aside #taglist' ).css('opacity', '1');
            jQuery( 'body.single-format-aside .widget-area' ).css('opacity', '1');
            jQuery( 'body.single-format-aside .site-footer' ).css('opacity', '1');
            jQuery( 'body.single-format-aside #respond' ).css('opacity', '1');
        }
    }

    pe_check_scroll_position();

    $window.scroll( pe_check_scroll_position );

});
