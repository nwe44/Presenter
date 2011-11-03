<?php

/*
**    ========================================================================
**    Presenter - a simple presentation system
**    ========================================================================
**    github.com/nwe44/Presenter
**
**    This is a program to enable non-technical ftp users to 
**    create a collection of image slideshows with attached comments.
**    
**    At my office there are a number of users who need to be able 
**    to create slideshows within our our domain. This means a number 
**    of limitations:
**
**    - No code coding knowledge may be required for usage
**    - No server technology (ie. ability to see/use .htaccess or databases) 
**      knowledge may be required
**    - Needs to be locally hosted
**
**    Most web hosts provide a gui for password protecting a directory, 
**    so this is what I'm assuming people will use for security. I can't 
**    imagine there are many options given the above constraints.
**
**
**    ========================================================================
**    Installation
**    ========================================================================
**
**    Upload index.php, the "contents" folder and the "_" folder to a 
**    public directory
**
**    Done.
**
**    ========================================================================
**    To create a presentation
**    ========================================================================
**
**    Each sub-directory of "contents" constitutes a presentation, so,
**
**    1. Drop images into a sub-directory of the contents folder.
**    2. Create a text file (with any name) in to the folder created in step 1. 
**       The contents of this file will be parsed as Markdown, and the h1 element 
**       will name the presentation.
**
*/



include_once "_/includes/markdown.php";


?><!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>CGP Presentation</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1, maximum-scale=1.0">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
  <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="_/css/style.css">
  
  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->
  <script src="_/js/libs/modernizr-2.0.6.min.js"></script>
</head>

<body><div id="container"><header><a class="header-icon header-icon-menu ir"></a></header><div id="main" role="main"></div><footer></footer></div>
<!-- That's it, this is one of those javascript sites. You'll want to look in the script files to see what's going on. :) -->

  <!-- JavaScript at the bottom for fast page loading -->
<?php

function getDirectory( $path = '.', $level = 0, $structure_array = array ()){ 

	$encoded_path = md5($path);

	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);	
	}


	$ignore = array( 'cgi-bin', '.', '..' ); 
	// Directories to ignore when listing output. Many hosts 
	// will deny PHP access to the cgi-bin. 
	
	$dh = @opendir( $path ); 
	// Open the directory to the handle $dh 

	while( false !== ( $file = readdir( $dh ) ) ){ 
	// Loop through the directory 

		if( !in_array( $file, $ignore ) ){ 
		// Check that this file is not to be ignored 

			if( is_dir( "$path/$file" ) ){ 
			// Its a directory, so we need to keep reading down... 
				$unique = md5($path . "/" . $file);
				// add the directory to our array
				$structure_array[$unique]['path'] = $path . "/" . $file;
				$structure_array[$unique]['uniqueId'] = $unique;

				// parse the directory
				$structure_array = getDirectory( "$path/$file", ($level+1), $structure_array); 

			} else {

				$fileInfo = array( 'url' => $path . "/" . $file);

				// corral our file info

				if (function_exists('finfo_open')) {
					$fileInfo['mimetype'] = finfo_file($finfo, $path . "/" .$file);
				} elseif (function_exists('mime_content_type')) { // backwards compatibilty
					$fileInfo['mimetype'] = mime_content_type($path . "/" . $file);
				} else { // a very basic fallback
					if (preg_match("/^.*\.jpg/", $file) || preg_match("/^.*\.jpeg/", $file)) {
						$fileInfo['mimetype'] = "image";
					} elseif (preg_match("/^.*\.txt/", $file) ||  preg_match("/^.*\.markdown/", $file)) {
						$fileInfo['mimetype'] = "text";
					}
				}

				if (stripos($fileInfo['mimetype'], "text") === 0) {
					$fileInfo['note'] = Markdown(file_get_contents($path . "/" . $file));
					$structure_array[$encoded_path]['notes'][] = $fileInfo;
				} elseif(stripos($fileInfo['mimetype'], "image") === 0) {
					$structure_array[$encoded_path]['images'][] = $path . "/" . $file;

				}

			} 
		}
	}
	if (function_exists('finfo_close')) {
		finfo_close($finfo);
	}

	closedir( $dh ); 
	return $structure_array;
	// Close the directory handle 

} 
$directories = json_encode(getDirectory('contents'));
echo "<script> var presentations = " . $directories . ";</script>";
?>
  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="_/js/libs/jquery-1.6.2.min.js"><\/script>')</script>


	<!-- scripts concatenated and minified via build script -->
	<script defer src="_/js/mylibs/carousel.js"></script>
	<script defer src="_/js/mylibs/underscore.js"></script>
	<script defer src="_/js/mylibs/jquery.tmpl.js"></script>
	<script defer src="_/js/mylibs/jquery.ba-bbq.js"></script>
	<script defer src="_/js/plugins.js"></script>
	<script defer src="_/js/script.js"></script>
	<!-- end scripts -->
	<script id="presentationTmpl" type="text/x-jquery-tmpl"> 
		<div class="horizontal-carousel horizontal-carousel-hidden slidewrap">
			<ul class="horizontal-carousel-slider slider">
				{{each images}}
				<li data-slideid="slide${$index}" class="horizontal-carousel-slide">
					<img src="${$value}" />&nbsp;
				</li>
				{{/each}}
			</ul>
		</div>
		<div class="ir throbber"></div>
	</script>

	<script id="mainNavTmpl" type="text/x-jquery-tmpl"> 
		
		<div class="nav-wrapper">
			<h3 class="title">Presentations</h3>
			<nav class="content">
				<ul class="main-nav">
				<li class="presentation-item">
					<a class="presentation-item-link nav-item-home" href="#" id="home-button" title="">Home</a>
				</li>
				{{each list}}
				{{if $value.id}}
				<li class="presentation-item">
					<span class="presentation-item-number">${$index + 1}</span><a class="presentation-item-link nav-item-presentation" href="#${$value.id}" id="${$value.uniqueId}" title="">${$value.title}</a>
				</li>
				{{/if}}
				{{/each}}
				</ul>
			</nav>
			<a class="header-icon header-icon-settings ir" href="#" id="settings-button" title="">Home</a>
			<a class="header-icon header-icon-close ir"></a>
		</div>
		<div class="popover popover-centered" id="settings-popover">
			<div class="inner">
				<h3 class="title">Settings</h3>
				<nav class="content">
					<ul class="settings-nav">
						<li class="nav-item">
							<a class="nav-item-link nav-item-viewport" href="#" id="Mini" title="">Make viewport mini</a>
						</li>
						<li class="nav-item">
							<a class="nav-item-link nav-item-viewport" href="#" id="Small" title="">Make viewport small</a>
						</li>
						<li class="nav-item">
							<a class="nav-item-link nav-item-viewport" href="#" id="Medium" title="">Make viewport medium</a>
						</li>
						<li class="nav-item">
							<a class="nav-item-link nav-item-viewport" href="#" id="Large" title="">Make viewport large</a>
						</li>
						<li class="nav-item">
							<a class="nav-item-link nav-item-viewport" href="#" id="Normal" title="">Reset viewport</a>
						</li>
					</ul>
				</nav>
			</div>
			<a class="header-icon header-icon-close ir"></a>
		</div>
	</script>

	<script id="noteTmpl" type="text/x-jquery-tmpl"> 
		<div class="popover-wrapper popover-wrapper-note">
			<div class="popover above popover-visible">
				<div class="arrow"></div>
				<div class="inner">
					<h3 class="title">${title}</h3>
					<div class="content">
					{{html content}}
					</div>
				</div>
				<a class="header-icon header-icon-close ir"></a>
			</div>
		</div>
	</script>
</body>
</html>



