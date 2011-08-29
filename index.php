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
	<h3>Navigation</h3>
	<a href="#" class="nav-toggle">Navigation toggle</a>
    </header>
    <div id="main" role="main">

    </div>
    <footer>

    </footer>
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

  <!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
       mathiasbynens.be/notes/async-analytics-snippet -->
  <script>
    var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
  </script>

  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
    <script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script defer>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->

</body>
</html>



