/* Author: Nick Evans

*/

var presenter = {

	status : {},

	init : function () {
		var $nav = this.generateNav();

		$('header').append($nav);

		$(".main-nav a").click(function(e){
			e.preventDefault();
			var href = $(this).attr( "href" );
			// Push this URL "state" onto the history hash.
		
			$.bbq.pushState({ p: href.substr(1),  s: "" });

			return false;
		});

		$('.nav-toggle').click(function(e){
			e.preventDefault();
			var state =  $.bbq.getState();

			// toggle the menu state
			// if the menu state is undefined
			// set to true to open it
			if (typeof(state.m) == "undefined") {
				console.log('was undefined');
				state.m = true;
			} else {
				state.m = (state.m == "true") ? false : true;
			}
			$.bbq.pushState(state);
			return false;
		});

		// Since the event is only triggered when the hash changes, we need
		// to trigger the event now, to handle the hash the page may have
		// loaded with.
		$(window).trigger( "hashchange" );
	},

	newMenuStatus : function () {
		$('header').toggleClass('minimized');
		$('body').toggleClass('header-visible');
		if ($('body').hasClass('header-visible')) {
			$('.note-minimized').removeClass('note-minimized');
		} else {
			$('.note').addClass('note-minimized');
		}
	},

	// reveal a slideshow
	revealSlideshow : function () {
		$('.throbber').remove();
		$('.horizontal-carousel').removeClass('horizontal-carousel-hidden');
	},

	// load some new content
	newContent : function (opts) {
		var that = this,
			status = that.status;

		$('#main').html('');
		$('.note-current').removeClass('note-current');
		$('#' + opts.presentationId + "-note").addClass('note-current');

		$('#presentationTmpl').tmpl(opts.presentation).appendTo('#main');

		$('.horizontal-carousel').imagesLoaded(presenter.revealSlideshow);

		$('#main .slidewrap').carousel({
			slider: '.horizontal-carousel-slider',
			slide: '.horizontal-carousel-slide',
			addPagination: true,
			addNav: true,
			callback: presenter.pushSlideNo,
			speed: 300 // ms.
		});

		if (opts.slideNo) {
			this.newSlide(opts.slideNo);
		}

		if (opts.menu == "true" && opts.menu != status.m) {
			that.newMenuStatus();
		}
	},

	// change slide position
	newSlide : function (slideNo) {
		$('.carousel-tabs li').eq(slideNo).find('a').click();
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
		var state = $.bbq.getState()
			index = $('.carousel-active-slide').index();
		// set the cached state so the hash change event 
		// doesn't fire based on this change

		presenter.status.s = index;
		$.bbq.pushState({ s: index });
	}

};

// Bind a callback that executes when document.location.hash changes.
$(window).bind( "hashchange", function(e) {

//	console.log('hash change');

	var hash = $.bbq.getState(),
		presentationId = hash.p,
		slideNo = hash.s,
		menu = hash.m,
		presentation = presentations[presentationId],
		status = presenter.status,
		present = presenter,
		liveContent = "";

	$(".main-nav a").removeClass('nav-item-link-active');
	$('#' + presentationId).addClass('nav-item-link-active');

	// now let's find out what changed

	// No presentation? We're on the front page
	if (!presentation) {
		$('header').removeClass('minimized');
		try {
			// md5('contents') === "98bf7d8c15784f0a3d63204441e1e2aa"
			// I know this is inellegant, but it's a temporary solution
			
			for (i = 0, l = presentations['98bf7d8c15784f0a3d63204441e1e2aa'].notes.length; i < l; i += 1) {
				liveContent += (presentations['98bf7d8c15784f0a3d63204441e1e2aa'].notes[i]).note;
			}
			$presentationNote = $('<div />', {
					'class': "note note-current",
					html: liveContent
					});
			$('body').addClass('header-visible').append($presentationNote);
		} catch (err) {
			console.log(err);
		}

	// no status? Then it's first page load via a link, run everything.
	// check there's somewhere to go, and if we're already there.
	} else if (!status || presentation && presentationId != status.p){
		present.newContent({
			presentation : presentation, 
			presentationId : presentationId, 
			slideNo : slideNo,
			menu : menu
			});

	// User has changed slide number
	// most likely by using the back button.
	} else if (slideNo != status.s) {
		present.newSlide(slideNo);

	// toggle the menu
	} else if (menu != status.m) {
		present.newMenuStatus();

	// error handling
	} else {
//		console.log('saw nothing', hash, status);
	}

	presenter.status = hash;
});

$(document).ready(function() { 
	presenter.init();
});