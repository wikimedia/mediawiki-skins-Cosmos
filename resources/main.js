( function( $, mw ) {

	// Naming conventions for variables:
	//
	// - Uppercase with underscores for constants
	// - Lowercase camelCase for normal variables
	// - Prefix with '$' if the variable/constant will store jQuery objects

		/* CONSTANTS */

	/**
	 * Various time units expressed as quantities of seconds
	 */
	const SECS = 1;
	const MINS = 60 * SECS;
	const HOURS = 60 * MINS;
	const DAYS = 24 * HOURS;

	/**
	 * The expiry time for cookies relating to the site notice,
	 * respectively
	 */
	const SITE_NOTICE_EXPIRY_TIME = 7 * DAYS;
		/* FUNCTIONS */

   $('.create-page-dialog__wrapper #create-page-dialog__title').keyup(function() {

        var empty = false;
        $('.create-page-dialog__wrapper #create-page-dialog__title').each(function() {
            if ($(this).val() === '') {
                empty = true;
            }
        });

        if (empty) {
            $('.create-page-dialog__button').prop('disabled', true);
        } else {
            $('.create-page-dialog__button').prop('disabled', false);
        }
    });

var modal = document.getElementById("createPageModal");
var btn = document.getElementById("createpage");
var span = document.getElementsByClassName("close")[0];
btn.onclick = function() {
  modal.style.display = "block";
}
span.onclick = function() {
  modal.style.display = "none";
}
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};     
mw.hook( 've.activationComplete' ).add( function () {
    $('.ve-activated .firstHeading').html($('title').html().replace(' - ' + mw.config.get("wgSiteName"), ''));
 var surface = ve.init.target.getSurface();
});
$.urlParam = function (name) {
    var results = new RegExp("[?&]" + name + "=([^&#]*)").exec(
      window.location.href
    );
    if (results === null) {
      return null;
    }
    return decodeURI(results[1]) || 0;
  };
  $.extend({
    _urlVars: null,
    getUrlVars: function () {
      if ($._urlVars === null) {
        var i,
          j,
          hash,
          hashes = window.location.search
            .slice(window.location.search.indexOf("?") + 1)
            .split("&");
        $._urlVars = {};
        for (i = 0, j = hashes.length; i < j; i++) {
          hash = hashes[i].split("=");
          $._urlVars[hash[0]] = hash[1];
        }
      }
      return $._urlVars;
    },
    getUrlVar: function (name) {
      return $.getUrlVars()[name];
    },
  });

  window.getElementsByClassName = function (oElm, strTagName, oClassNames) {
    var arrReturnElements = [];
    if (!oElm) {
      return arrReturnElements;
    }
    if (typeof oElm.getElementsByClassName == "function") {
      var arrNativeReturn = oElm.getElementsByClassName(oClassNames);
      if (strTagName == "*") {
        return arrNativeReturn;
      }
      for (var h = 0; h < arrNativeReturn.length; h++) {
        if (
          arrNativeReturn[h].tagName.toLowerCase() == strTagName.toLowerCase()
        ) {
          arrReturnElements[arrReturnElements.length] = arrNativeReturn[h];
        }
      }
      return arrReturnElements;
    }
    var arrElements =
      strTagName == "*" && oElm.all
        ? oElm.all
        : oElm.getElementsByTagName(strTagName);
    var arrRegExpClassNames = [];
    if (typeof oClassNames == "object") {
      for (var i = 0; i < oClassNames.length; i++) {
        arrRegExpClassNames[arrRegExpClassNames.length] = new RegExp(
          "(^|\\s)" + oClassNames[i].replace(/\-/g, "\\-") + "(\\s|$)"
        );
      }
    } else {
      arrRegExpClassNames[arrRegExpClassNames.length] = new RegExp(
        "(^|\\s)" + oClassNames.replace(/\-/g, "\\-") + "(\\s|$)"
      );
    }
    var oElement;
    var bMatchesAll;
    for (var j = 0; j < arrElements.length; j++) {
      oElement = arrElements[j];
      bMatchesAll = !0;
      for (var k = 0; k < arrRegExpClassNames.length; k++) {
        if (!arrRegExpClassNames[k].test(oElement.className)) {
          bMatchesAll = !1;
          break;
        }
      }
      if (bMatchesAll) {
        arrReturnElements[arrReturnElements.length] = oElement;
      }
    }
    return arrReturnElements;
  };

	
	
	/**
	 * Updates the height of the footer, in order to make sure it always fills
	 * the space between the bottom of the page, and the bottom of the viewport,
	 * regardless of how small the page is
	 */
	function updateFooterHeight() {
		var $footer = $( '#cosmos-footer' );
		// Reset the footer height to its default value
		$footer.height( 'auto' );
		if ( $(window).height() > $footer.offset().top + $footer.outerHeight( false )) {
			// If the footer is not large enough to fill the bottom of the page,
			// resize its outer height accordingly
			$footer.outerHeight( $(window).height() - $footer.offset().top, false );
		}
	}
	
	/**
	 * Closes the site notice
	 */
	function closeSiteNotice() {
		var $siteNotice = $( '#cosmos-content-siteNotice' );
		$siteNotice.remove();
		mw.cookie.set( 'CosmosSiteNoticeState', 'closed', { expires: SITE_NOTICE_EXPIRY_TIME } );
	}

	$( document ).ready( function () {
		$( '#cosmos-siteNotice-closeButton' ).click( closeSiteNotice );
		updateFooterHeight();
	} );
	
    	// On window resize, update the footer height if necessary
	$( window ).resize( updateFooterHeight );
	
} )( jQuery, mediaWiki );
