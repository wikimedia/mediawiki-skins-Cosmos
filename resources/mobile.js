if ( $( window ).width() < 851 ) {
    $('.cosmos-search-input').focus(function () {
             $('#cosmos-banner-userOptions').hide();
             $('#cosmos-search-buttonContainer').show();
   });
    $('.cosmos-search-input').focusout(function () {
         $('#cosmos-banner-userOptions').show();
         $('#cosmos-search-buttonContainer').hide();
   });
}
if ( $( window ).width() > 850 ) {
    $('.cosmos-mobile-navigation').remove();
}
