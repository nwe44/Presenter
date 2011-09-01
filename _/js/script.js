/* Author: Nick Evans

*/

var presenter = {

	status : {},

	init : function () {
		this.generateNav();

		$(".main-nav a").click(function(e){
			e.preventDefault();
			var href = $(this).attr( "href" );
			// Push this URL "state" onto the history hash.
			$('.header-icon-menu').click();
			$.bbq.pushState({ p: href.substr(1),  s: "" });

			return false;
		});

		$('.header-icon-menu').click(function(e){
			e.preventDefault();
			presenter.newMenuStatus();
			return false;
		});
		$('.header-icon-note').live('click', function(e){
			e.preventDefault();
			if ($('.popover-wrapper-nav .popover').hasClass('popover-visible')) {
				presenter.newMenuStatus();
			}

			$('.popover-wrapper-note .popover').toggleClass('popover-visible')
			return false;
		});
		$('.header-icon-close').live('click', function(e){
			e.preventDefault();
			$(this).parent().removeClass('popover-visible');
			return false;
		});



		// Since the event is only triggered when the hash changes, we need
		// to trigger the event now, to handle the hash the page may have
		// loaded with.
		$(window).trigger( "hashchange" );
	},

	newMenuStatus : function () {
		$('.popover-wrapper-nav .popover').toggleClass('popover-visible');
		if ($('.popover-wrapper-nav .popover').hasClass('popover-visible')) {
			$('.popover-wrapper-note .popover').removeClass('popover-visible');
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
			status = that.status,
			sortable = [];

		if (!opts.presentation.sortedImages) {
			// TODO Someday my php will be good enough that I don't need to do this.
			for (var fileName in opts.presentation.images) {
				sortable.push(opts.presentation.images[fileName]);
			}
			opts.presentation.sortedImages = sortable.sort();
		}

		$('#main').html('');
		$('.popover-wrapper-note').remove();

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

		// add the notes
		this.newNote(opts.presentationId);

		if (opts.slideNo) {
			this.newSlide(opts.slideNo);
		}
	},

	// change slide position
	// TODO: should this function be named "nextSlide" ?
	newSlide : function (slideNo) {
		$('.carousel-tabs li').eq(slideNo).find('a').click();
	},

	// build a new note
	newNote : function (id) {
		var presentation = presentations[id],
			note = {},
			titleRegEx = new RegExp("(<h1[^>]*>(.*)</h1>)");

		note.content = (presentation.notes[0]).note;
		note.title = titleRegEx.exec(note.content);
		note.title = note.title[note.title.length - 1];

		// remove the old note, if any
		$('.popover-wrapper-note').remove();

		// add a toggle switch if there isn't one
		if (! $('header .header-icon-note').length) {
			$('header').append('<a class="header-icon header-icon-note ir"></a>');
		}

		// place the new note
		$('#noteTmpl').tmpl(note).appendTo('header');
	},

	// build the nav and append it to the header
	// TODO: consider building the original object to remove the need for this
	generateNav : function () {
		var processedPresentations = { list : [] };

		// orgainize a new object that's easier to work with in jQuery tmpl
		// in other words; an array
		for (var key in presentations) {
			if (presentations.hasOwnProperty(key) && presentations[key].path) {
				var presentation = presentations[key],
					note = (presentation.notes[0]).note,
					titleRegEx = new RegExp("(<h1[^>]*>(.*)</h1>)");

				presentation.title = titleRegEx.exec(note);
				presentation.title = presentation.title[presentation.title.length - 1];
				presentation.id = key;
				processedPresentations.list.push(presentation);
			}
		}

		$('#mainNavTmpl').tmpl(processedPresentations).appendTo('header');
	},

	pushSlideNo : function () {
		var state = $.bbq.getState(),
			index = $('.carousel-active-slide').index();
		// set the cached state so the hash change event 
		// doesn't fire based on this change

		presenter.status.s = index;
		$.bbq.pushState({ s: index });
	}

};

// Bind a callback that executes when document.location.hash changes.
$(window).bind( "hashchange", function(e) {

	var hash = $.bbq.getState(),
		presentationId = hash.p,
		slideNo = hash.s,
		presentation = presentations[presentationId],
		status = presenter.status,
		present = presenter,
		liveContent = "";

	$(".main-nav a").removeClass('nav-item-link-active');
	$('#' + presentationId).addClass('nav-item-link-active');

	// now let's find out what changed

	// No presentation? We're on the front page
	if (!presentation) {
		$('.popover-wrapper-nav .popover').addClass('popover-visible');
		try {
			for (var i = 0, l = presentations['contents'].notes.length; i < l; i += 1) {
				liveContent += (presentations['contents'].notes[i]).note;
			}

			$('#main').html("<div class='page-border'></div><div class='front-page'><div class='front-page-wrapper'><div class='front-page-page'>" + liveContent + "</div></div></div>");
		} catch (err) {
			console.log(err);
		}

	// no status? Then it's first page load via a link, run everything.
	// check there's somewhere to go, and if we're already there.
	} else if (!status || presentation && presentationId != status.p){
		present.newContent({
			presentation : presentation, 
			presentationId : presentationId, 
			slideNo : slideNo
			});

	// User has changed slide number
	// most likely by using the back button.
	} else if (slideNo != status.s) {
		present.newSlide(slideNo);

	// error handling
	} else {
//		console.log('saw nothing', hash, status);
	}

	presenter.status = hash;
});

$(document).ready(function() { 
	presenter.init();
});
