(function( $ ) {
    'use strict';


     $(function() {
        $('input[name=sms-send-code]').on('click', function(e) {
            $.ajax({
                type: 'POST',
                url: loginPage.ajaxUrl + '?action=' + loginPage.ajaxAction,
                data: {
                    'wp-auth-nonce': $('input[name=wp-auth-nonce]').val(),
                    'wp-auth-id': $('input[name=wp-auth-id]').val(),
                    newmobile:   $('input[name=newmobile]').val(),
                },
                success: $.proxy(function( e ) {
                    $('.two-factor-extensions-otp.hidden').removeClass('hidden');
                    $('.two-factor-extensions-message.hidden').removeClass('hidden');
                    $('#wp-submit').removeClass('hidden');
                    $(this).val( loginPage.resendCodeLabel );
                }, this),
                error: function( e ) {
                    $('.two-factor-extensions-message.hidden').text( 'Error message' );
                    $('.two-factor-extensions-message.hidden').removeClass('hidden');
                },
            });
            e.preventDefault();
        });
     });

})( jQuery );
