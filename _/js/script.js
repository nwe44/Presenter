/* Author: Nick Evans

*/

var presenter = {

	init : function () {
		var liveContent = "",
			that = this,
			$nav = that.generateNav();

		$('header').append($nav);

		$(".main-nav a").click(function(e){
			e.preventDefault();
			var href = $(this).attr( "href" );
			// Push this URL "state" onto the history hash.

			$.bbq.pushState({ p: href.substr(1),  s: "" });

			// Prevent the default click behavior.
			return false;
		});

		$('.nav-toggle').click(function(e){
			e.preventDefault();
			// Push this URL "state" onto the history hash.

			$('header').toggleClass('minimized');
			$('body').toggleClass('header-visible');
			if ($('body').hasClass('header-visible')) {
				$('.note-minimized').removeClass('note-minimized');
			} else {
				$('.note').addClass('note-minimized');
			}
			
			return false;
		});

		// Bind a callback that executes when document.location.hash changes.
		$(window).bind( "hashchange", function(e) {

			var presentationId = $.bbq.getState( "p" ),
				slideNo = $.bbq.getState( "s" ),
				presentation = presentations[presentationId];

			$(".main-nav a").removeClass('nav-item-link-active');
			$('#' + presentationId).addClass('nav-item-link-active');

			// check there's somewhere to go, and if we're already there.
			if (presentation && presentations.currentPresentation != presentationId) {

				presentations.currentPresentation = presentationId;
				$('#main').html('');
				$('.note-current').removeClass('note-current')
				$('#' + presentationId + "-note").addClass('note-current');


				$('#presentationTmpl').tmpl(presentation).appendTo('#main');

				$('.horizontal-carousel').imagesLoaded(presenter.revealSlideshow);

				$('#main .slidewrap').carousel({
					slider: '.horizontal-carousel-slider',
					slide: '.horizontal-carousel-slide',
					addPagination: true,
					addNav: true,
					callback: that.pushSlideNo,
					speed: 300 // ms.
				});
				if (slideNo) {
					$('.carousel-tabs li').eq(slideNo).find('a').click();
				}

			// assume we've just changed a slide number
			} else if (presentation) {

				$('.carousel-tabs li').eq(slideNo).find('a').click();

			// md5('contents') === "98bf7d8c15784f0a3d63204441e1e2aa"
			// I know this is inellegant, but it's a temporary solution
			}else if (presentations['98bf7d8c15784f0a3d63204441e1e2aa']) {
				$('header').removeClass('minimized');
				for (i = 0, l = presentations['98bf7d8c15784f0a3d63204441e1e2aa'].notes.length; i < l; i += 1) {
					liveContent += (presentations['98bf7d8c15784f0a3d63204441e1e2aa'].notes[i]).note;
				}
				$presentationNote = $('<div />', {
						'class': "note note-current",
						html: liveContent
						});
				$('body').addClass('header-visible').append($presentationNote);

		}

		});

		// Since the event is only triggered when the hash changes, we need
		// to trigger the event now, to handle the hash the page may have
		// loaded with.
		$(window).trigger( "hashchange" );
	},

	revealSlideshow : function () {
		$('.throbber').remove();
		$('.horizontal-carousel').removeClass('horizontal-carousel-hidden');
	},

	// build the nav
	// TODO: convert this to jQuery TMPL to separate concerns
	generateNav : function () {
		var $presentation,
			$presentationNote,
			$presentationNoteLink,
			$presentationLink,
			$nav = $("<ul />", {'class' : 'main-nav'});

		for (var key in presentations) {

			if (presentations.hasOwnProperty(key) && presentations[key].path) {
				var obj = presentations[key],
					notes = obj.notes,
					title = "";

				$presentation = $('<li />', {
					'class': "nav-item"});

				if (typeof(notes) != "undefined") {
					$presentationNote = $('<div />', {
						'class': "note",
						'id': key + "-note",
						html: (notes[0]).note});

					title = $presentationNote.find('h1').text() || $((notes[0]).note);

					$presentationLink = $('<a />', {
						'class': "nav-item-link",
						'href': "#" + key,
						'id': key,
						text: title});


					$('body')
						.append($presentationNote);
				} else {
					$presentationLink = $('<a />', {
						'id': key,
						'class': "nav-item-link",
						'href': "#" + key,
						text: key});
				}

				$presentation
					.prepend($presentationLink);

				$nav.append($presentation);
			}
		}
		return $nav;
	},

	pushSlideNo : function () {
		$.bbq.pushState({ s: $('.carousel-active-slide').index() });
	}

};



$(document).ready(function() { 
	presenter.init();
});