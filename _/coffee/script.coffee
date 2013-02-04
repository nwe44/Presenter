class Presenter
	status : {}

	init : ->
		@generateNav()

		@listenForKeyEvents()

		# Bind a callback that executes when document.location.hash changes.
		$(window).bind "hashchange", (e) =>
			@router e

		$(".nav-item-presentation").click (e) =>
			e.preventDefault()
			href = $(e.target).attr("href")
			$('.header-icon-menu').click()
			@status = {}
			$.bbq.pushState({ p: href.substr 1})

		$(".nav-item-viewport").click (e) ->
			e.preventDefault()
			$.bbq.pushState({ v: $(@).attr("id")})

		$(".header-icon-settings").click (e) ->
			e.preventDefault()
			log "called"
			$('.header-icon-menu').click()
			$('#settings-popover').addClass 'popover-visible'

		$(".nav-item-home").click (e) =>
			e.preventDefault()
			$.bbq.removeState()
			@newMenuStatus()

		$(".header-icon-menu").click (e) =>
			e.preventDefault()
			@newMenuStatus()

		$(".header-icon-close").click (e) ->
			e.preventDefault()
			$(@).parent().removeClass 'popover-visible nav-visible'

		# Since the event is only triggered when the hash changes, we need
		# to trigger the event now, to handle the hash the page may have
		# loaded with.
		$(window).trigger "hashchange"

	makeViewportNormal : ->
		$('.horizontal-carousel')
		.css(
			'width': '100%'
			'left': "0px"
			'margin-left' : "0px"
		).removeClass 'horizontal-carousel-sized'

	makeViewportMini : ->
		$('.horizontal-carousel')
		.css(
			'width': '480px'
			'left': "50%"
			'margin-left' : "-240px"
		).addClass 'horizontal-carousel-sized'

	makeViewportSmall : ->
		$('.horizontal-carousel')
		.css(
			'width': '768px'
			'left': "50%"
			'margin-left' : "-384px"
		).addClass 'horizontal-carousel-sized'

	makeViewportMedium : ->
		$('.horizontal-carousel')
		.css(
			'width': '1024px'
			'left': "50%"
			'margin-left' : "-512px"
		).addClass 'horizontal-carousel-sized'

	makeViewportLarge : ->
		$('.horizontal-carousel')
		.css(
			'width': '1280px'
			'left': "50%"
			'margin-left' : "-640px"
		).addClass 'horizontal-carousel-sized'

	newMenuStatus : ->
		$('.nav-wrapper').toggleClass 'nav-visible'
		if $('.nav-wrapper').hasClass 'nav-visible'
			$('.popover-wrapper .popover').removeClass 'popover-visible'

	# reveal a slideshow
	revealSlideshow : ->
		$('.throbber').remove()
		$('.horizontal-carousel').removeClass 'horizontal-carousel-hidden'

	# load some new content
	newContent : (opts) ->

		# make sure everything's in order
		opts.presentation.images.alphanumSort()

		# highlight the active link
		$(".main-nav a").removeClass 'nav-item-link-active'

		$("##{opts.presentation.uniqueId}").addClass 'nav-item-link-active'

		$('#main').html ''

		$('.popover-wrapper-note').remove()

		$('#presentationTmpl').tmpl(opts.presentation).appendTo '#main'

		$('.horizontal-carousel').imagesLoaded @revealSlideshow

		$('#main .slidewrap').carousel(
			slider: '.horizontal-carousel-slider'
			slide: '.horizontal-carousel-slide'
			addPagination: true
			addNav: true
			callback: @pushSlideNo
			speed: 300 # ms.
		)

		# add the notes
		@newNote opts.presentationId

		if opts.slideNo
			@newSlide opts.slideNo

	newFrontPage : ->
		liveContent = ""
		$('.popover-wrapper-nav .popover').addClass 'popover-visible'
		try ## md5(contents) == "98bf7d8c15784f0a3d63204441e1e2aa"
			for  metaData in presentations["98bf7d8c15784f0a3d63204441e1e2aa"].notes
				liveContent += metaData.note

			$('#main').html """
				<div class='page-border'></div>
				<div class='front-page'>
					<div class='front-page-wrapper'>
						<div class='front-page-page'>#{liveContent}</div>
					</div>
				</div>
				"""
			$("header a.nav-item-link-active").removeClass('nav-item-link-active');
			$('#home-button').addClass('nav-item-link-active');
		catch err
			console.log(err)

	# change slide position
	# TODO: should this function be named "nextSlide" ?
	newSlide : (slideNo) ->
		$('.carousel-tabs li').eq(slideNo).find('a').attr('aria-selected', 'true').click()

	# build a new note
	newNote : (id) ->
		presentation = presentations[id]
		note = {}
		titleRegEx = new RegExp("(<h1[^>]*>(.*)</h1>)")

		try
			note.content = (presentation.notes[0]).note
			note.title = titleRegEx.exec note.content
			note.title = note.title[note.title.length - 1]
		catch err
			log err, presentation,  "Can't find a text file for the presentation in #{presentation.path}"
			note.title = presentation.path

		# remove the old note, if any
		$('.popover-wrapper-note').remove()

		# add a toggle switch if there isn't one
		if ! $('header .header-icon-note').length
			$('header').append '<a class="header-icon header-icon-note ir"></a>'

		# place the new note
		$('#noteTmpl').tmpl(note).appendTo('header');

		# iOS doesn't like 'live' so attaching click events here.
		$('.header-icon-note').click (e) ->
			e.preventDefault()
			if  $('.popover-wrapper-nav .popover').hasClass('popover-visible')
				@newMenuStatus()

			$('.popover-wrapper-note .popover').toggleClass 'popover-visible'


		$('.header-icon-close').click (e) ->
			e.preventDefault()
			$(@).parent().removeClass 'popover-visible'

	# build the nav and append it to the header
	# TODO: consider building the original object to remove the need for this
	generateNav : ->
		processedPresentations = { list : [] }

		# orgainize a new object that's easier to work with in jQuery tmpl
		# in other words; an array
		for own key, presentation of presentations
			if presentation.path
				try
					note = (presentation.notes[0]).note
					titleRegEx = new RegExp("(<h1[^>]*>(.*)</h1>)")
					presentation.title = titleRegEx.exec note
					presentation.title = presentation.title[presentation.title.length - 1]
				catch err
					# looks like there's no note
					log err, presentations[key], "Can't find a text file for the presentation in #{(presentations[key]).path}"
					presentation.title = presentation.path;

				presentation.id = key;
				processedPresentations.list.push presentation

		indexReference = _.pluck processedPresentations.list, "title"
		indexReference.alphanumSort()

		processedPresentations.list = _.sortBy processedPresentations.list, (myListElement) ->
			_.indexOf indexReference, myListElement.title


		$('#mainNavTmpl').tmpl(processedPresentations).appendTo 'body'

	pushSlideNo : ->
		state = $.bbq.getState()
		index = $('.carousel-active-slide').index
		# set the cached state so the hash change event
		# doesn't fire based on this change
		@status.s = index
		$.bbq.pushState s: index

	keyEventHandler : (e) =>
		state = $.bbq.getState()
		currentPresentation = @status.p

		switch e.keyCode
			when 39, 13, 32, 34 # Right arrow, Enter, Space, Page down
				state.s = state.s || 0
				if presentations[currentPresentation] and presentations[currentPresentation].images.length - 1 > state.s
					state.s++
					$.bbq.pushState state
					event.preventDefault()
				break
			when 37, 8, 33 # Left arrow, Backspace,  Page Up
				if state.s > 0
					state.s--
					$.bbq.pushState state
					event.preventDefault()
				break

	listenForKeyEvents : ->
		if document.addEventListener
			document.addEventListener 'keydown', @keyEventHandler, false
		else if document.attachEvent
			document.attachEvent 'keydown', @keyEventHandler

	router : (e) ->
		hash = $.bbq.getState()
		presentationId = hash.p
		slideNo = hash.s
		viewportSize = hash.v
		presentation = presentations[presentationId]

		# now let's find out what changed

		# No presentation? We're on the front page
		if  !presentation
			@newFrontPage()

		# no status? Then it's first page load via a link, run everything.
		# check there's somewhere to go, and if we're already there.
		else if !@status.p
			@newContent
				presentation : presentation
				presentationId : presentationId
				slideNo : slideNo
		# new presentationID, let's load a new presentation
		else if presentation and presentationId != @status.p

			# reset slide number to zero ... quietly
			$(window).unbind 'hashchange', @router
			$.bbq.pushState s: 0
			$(window).bind 'hashchange', @router

			@newContent
				presentation : presentation
				presentationId : presentationId
				slideNo : 0

		# User has changed slide number
		# most likely by using the back button.
		else if slideNo != @status.s
			@newSlide slideNo

		# error handling
		else
			log 'saw nothing', hash, @status
		if viewportSize != @status.v and typeof(viewportSize) != "undefined"
			@["makeViewport#{viewportSize}"]()


		@status = hash
	# end of router()

presenter = new Presenter()

$(document).ready ->
	# preload throbber
	if document.images
		img1 = new Image();
		img1.src = "_/img/ajax-loader.gif"

	presenter.init()
