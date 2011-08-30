<?php

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

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>CGP Presentation</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <link rel="stylesheet" href="_/css/style.css">
  
  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except this Modernizr build incl. Respond.js
       Respond is a polyfill for min/max-width media queries. Modernizr enables HTML5 elements & feature detects; 
       for optimal performance, create your own custom Modernizr build: www.modernizr.com/download/ -->
  <script src="_/js/libs/modernizr-2.0.6.min.js"></script>
</head>

<body>

	<div id="container">
		<header class="minimized">
			<a class="header-icon header-icon-menu ir"></a>
		</header>

		<div id="main" role="main"></div>
  </div> <!--! end of #container -->


  <!-- JavaScript at the bottom for fast page loading -->
<?php

function getDirectory( $path = '.', $level = 0, $structure_array = array ()){ 

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

				// add the directory to our array
				$structure_array[md5($path . "/" . $file)]['path'] = $path . "/" . $file;

				// parse the directory
				$structure_array = getDirectory( "$path/$file", ($level+1), $structure_array); 

			} else {

				if (function_exists('finfo_open')) {
					// corral our file info
					$fileInfo = array(
						'filename' => $file, 
						'url' => $path . "/" . $file,
						'mimetype' => finfo_file($finfo, $path . "/" .$file)
						);
				} else { // backwards compatibilty
					$fileInfo = array(
						'filename' => $file, 
						'url' => $path . "/" . $file,
						'mimetype' => mime_content_type($path . "/" .$file)
						);				
				
				}

				if (stripos($fileInfo['mimetype'], "text") === 0) {
					$fileInfo['note'] = Markdown(file_get_contents($path . "/" . $file));
					$structure_array[md5($path)]['notes'][] = $fileInfo;
				} elseif(stripos($fileInfo['mimetype'], "image") === 0) {
					$structure_array[md5($path)]['images'][] = $fileInfo;

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
	<script defer src="_/js/mylibs/jquery.tmpl.js"></script>
	<script defer src="_/js/mylibs/jquery.ba-bbq.js"></script>
	<script defer src="_/js/plugins.js"></script>
	<script defer src="_/js/script.js"></script>
	<!-- end scripts -->
	<script id="presentationTmpl" type="text/x-jquery-tmpl"> 
		<div class="horizontal-carousel horizontal-carousel-hidden slidewrap">
			<ul class="horizontal-carousel-slider slider">
				{{each images}}
				<li id="slide${$index}" class="horizontal-carousel-slide">
					<img src="${$value.url}" />&nbsp;
				</li>
				{{/each}}
			</ul>
		</div>
		<div class="ir throbber"></div>
	</script>

	<script id="mainNavTmpl" type="text/x-jquery-tmpl"> 
		<div class="popover-wrapper popover-wrapper-nav">
			<div class="popover above popover-visible">
				<div class="arrow"></div>
				<div class="inner">
					<h3 class="title">Presentations</h3>
					<nav class="content">
						<ul class="main-nav">
						{{each presentations}}
						{{if $value.id}}
						<li class="nav-item">
							<a class="nav-item-link" href="#${$value.id}" id="${$value.id}" title="">${$value.title}</a>
						</li>
						{{/if}}
						{{/each}}
						</ul>
					</nav>
				</div>
			</div>
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
			</div>
		</div>
		<a class="header-icon header-icon-note ir"></a>
	</script>


</body>
</html>



