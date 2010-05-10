<?php if (!defined('FARI')) die();

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Setup error reporting and register a shutdown function passign errors to the Fari_ApplicationDiagnostics display.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */

// set error reporting
error_reporting(E_ALL);
// hide displaying of errors, we will register a shutdown function...
ini_set('display_errors', 0);

/**
 * Fire up a diagnostics display or production server message if an error is raised.
 * @return void
 */
function shutdown() {
    if (($error = error_get_last())) {
        extract($error);
        switch($type) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                // show diagnostics display
                if (REPORT_ERROR) Fari_ApplicationDiagnostics::display('PHP Error', $file, $line, $message.'.');
                // show message on a production server
                else Fari_ApplicationDiagnostics::productionMessage($message);
                break;
        }
    }
}
// register our 'error handler'
register_shutdown_function('shutdown');



/********************* exception handler *********************/



/**
 * Exceptions handler for the application. Will call Fari_ApplicationDiagnostics for display.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework
 */
class Fari_Exception extends Exception {
	
	/**
	 * Fire up a display with error message, sourcecode and some trace.
	 * @return void
	 */
	public function fire() {
		// get the specifics of the error
		// where was the error thrown
		$file = $this->getFile();
		// line with the error
		$line = $this->getLine();
		// message we are outputing
		$message = $this->getMessage();
		// trace of error
		$trace = $this->getTrace();
		
		Fari_ApplicationDiagnostics::display('Fari Exception', $file, $line, $message, $trace);
	}
	
}



/********************* diagnostics display *********************/



/**
 * Diagnostics, working as a wrapper to provide a nice display for Errors and Exceptions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework
 */
class Fari_ApplicationDiagnostics {

	/**
	 * Dumps variables into the view.
	 */
    public static function dump($mixed, $title='Variable Dump') {
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
            <title><?php echo $title; ?></title>
            <?php self::css();  ?>
        </head>

        <body>
            <div id="title"><b><?php echo FARI; ?></b> running <b><?php echo APP_VERSION; ?></b></div>
            <div id="message"><h1><?php echo $title; ?></h1></div>
            <div id="box"><pre><?php echo self::formatVars($mixed); ?></pre></div>
            </body>
            </html>
        <?php
        die();
    }

    /**
     * Format mixed variables for output
     * @param <type> $mixed
     * @return <type>
     */
    public static function formatVars($mixed) {
        // we are working in HTML context
        $mixed = Fari_Escape::html($mixed);
        if ($mixed == NULL) $mixed = '<em>NULL</em>';
        else if (empty($mixed)) $mixed = '<em>empty</em>';
        else $mixed = print_r($mixed, TRUE);

        return $mixed;
    }

	/**
	 * Display Error or an Exception.
	 * @param string $type PHP Error or Fari Exception
	 * @param string $file File where the error was thrown
	 * @param string $line Line on which the error was thrown
	 * @param string $message Message of the error
	 */
	public static function display($type, $file, $line, $message, $trace=NULL) {
		// clean output
		ob_end_clean();
		
		// are we on a production server?
		if (!REPORT_ERR) self::productionMessage($message);

        // an Ajax request, display a lightweight message
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            die("Fari Exception on line $line in \"$file\": $message");
        }
		
		// 'build' the header
		self::showHeader();
		
		// output the message to the user
		echo '<div id="message"><h1>' . $type . '</h1><br />' . $message . '</div>';
		
		// output information about the file
		echo '<div id="file">File: <b>' . $file . '</b> Line: <b>' . $line . '</b></div>';
		
		// show the source
		self::showErrorSource($file, $line);
		
		// show trace if present
		if (!empty($trace)) self::showErrorTrace($trace);
		
		// show declared classes
		self::showDeclaredClasses();
		
		// close the whole page properly
		echo '</body></html>';
		
		// end the misery...
		die();
	}

    /**
     * Form diagnostics display header.
     */
	private static function showHeader() {
	?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
			<title>Fari Diagnostics</title>
            <?php self::css();  ?>
			<?php self::jsToggle();  ?>
		</head>
		<body>
            <div id="title"><b><?php echo FARI; ?></b> running <b><?php echo APP_VERSION; ?></b></div>
	<?php
	}



    /********************* diagnostics helpers *********************/



	/**
	 * Output a syntax highlighted sourcecode.
	 * @param string $errorFile Is the file where exception was thrown
	 * @param string $errorLine Line we want to highlight as troublesome
	 * @param string $displayRange How many source code lines before and after error line to show
	 * @param string $divId So that we can show/hide some source and then call it via js
	 */
	static function showErrorSource($errorFile, $errorLine, $displayRange=6, $divId=0) {
        // set color of the highlighting
		ini_set('highlight.string',	'#080');
		ini_set('highlight.comment',	'#999; font-style: italic');
		ini_set('highlight.default',	'#33393c');
		ini_set('highlight.html',	'#06b');
		ini_set('highlight.keyword',	'#d24; font-weight: bold');

        // get the source code into a string
		$sourceCode = highlight_file($errorFile, TRUE);
		// split into an array so that we can extract lines
		$sourceCode = explode('<br />', $sourceCode);
		
		// where (which line) to start showing source code? (start at line 1 ;)
		$beginLine = max(1, $errorLine - $displayRange);
		// where to stop?
		$endLine = min($errorLine + $displayRange, count($sourceCode));
		
		// open div with the error message
		if ($divId != '0') echo '<div id="' . $divId . '" class="code" style="display:none;">';
		else echo '<div id="' . $divId . '" class="code">';
		
		// highlighting might have started before we 'cut' it
		// set pointer to the beginning of our output
		$pointer = $beginLine;
		// while we haven't reached the start of file...
		while ($pointer-- > 0) {
			/**
			 * Match unlimited times any character that is not a \n
			 * Capture into backreference </span> OR <span *>.
			 */
			if (preg_match('%.*(</?span[^\>]*>)%', $sourceCode[$pointer], $match)) {
				// echo the highlighting tag if we've started it and not closed it
				if ($match[1] !== '</span>') echo $match[1];
				break;
			}
		}
		
		// paint the code
		// set pointer to the beginning of our output
		$pointer = $beginLine-1;
		// while we haven't reached the end of output...
		while (++$pointer <= $endLine) {
			// take our line from the source
			$line = $sourceCode[$pointer-1];
			// highlight our error line
			if ($pointer == $errorLine) {
				// strip formatting
				$line = strip_tags($line);
				// add tags
				echo '<h1 class="error"><span class="num err">' . $pointer . ':</span> &nbsp;&nbsp;&nbsp; ' . $line . '</h1>';
			// and output sourcecode line with delimiter
			} else echo '<span class="num">' . $pointer . ':</span> &nbsp;&nbsp;&nbsp; ' . $line . "<br />\n";
		}
		
		// close div
		echo '</div>';
	}
	
	/**
	 * Build a trace display with sourcecodes and all.
	 * @param array $errorTrace Contains an array with the trace as thrown
	 */
	private static function showErrorTrace(array $errorTrace) {
		// header
		echo '<div id="box"><b>Trace:</b>';
		// start the counter, we are humans so from 1
		$counter = 1;
		// traverse the array
		foreach ($errorTrace as $key => $row) {
			extract($row);
			echo '<br />';
			if (isset($file)) echo '<b>' . $counter . '.</b>&nbsp;&nbsp;' . $file;
			if (isset($line)) echo '&nbsp;&nbsp;(' . $line . ')';
			if (isset($function)) echo '&nbsp;&nbsp;' . $function . '()&nbsp;&nbsp;';
			
			// link to a javascript function that shows/hides the code listing
			echo '<a href="" onclick="toggle(\'' . $counter . '\');return false;" >source</a>';
			// add sourcecode listing
			self::showErrorSource($file, $line, 6, $counter);
			$counter++; // add to the counter
		}
		echo "\n</div>"; // close her up
	}
	
	/**
	 * Shows declared classes and their descriptions.
	 * @return void
	 */
	private static function showDeclaredClasses() {
		// get declared classes in the order they were declared
		$declaredClasses = get_declared_classes();
		// show only application related classes, Fari_Exception is implemented if we can see this :)
		// a pointer to start from
		$pointer = array_search('Fari_Exception', $declaredClasses) - 1;
		
		// header
		echo '<div id="box"><b>Declared Classes:</b><table>';
		// go through the array...
		$classCount = count($declaredClasses);
		while ($pointer++ < $classCount) {
			// get class name
			$class = @$declaredClasses[$pointer];
			// output it
			echo "\n<tr><td>" . $class . '</td><td><i>';
			
			// get description if is implemented
                        @eval('if (method_exists($class, "_desc")) echo $class::_desc();');
                        // if (method_exists($class, '_desc')) echo $class::_desc(); // use from PHP 5.3.0
			
			// close description
			echo '</i></td</tr>';
		}
		// close her up
		echo '</table></div>';
	}
	
	/**
	 * Is called when we are on a production server and don't want to show the source code.
	 * @param string $errorMessage Message Thrown
	 */
	public static function productionMessage($errorMessage) {
	?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
            <title><?php echo APP_VERSION; ?> Error</title>
            <?php echo self::productionCSS(); ?>
        </head>
        <body>
            <div id="modal">
                <h1>We are sorry...</h1>
                <p>You would help us if you told us what were you doing at the time the error happened, we can
                    then locate and fix the problem as soon as possible. Again, we are sorry for the
                    inconvenience.</p>
                <hr />
                <p><a href="javascript:history.go(-1)">Go back a page</a></p>
            </div>
        </body>
        </html>
	<?php
		// end the misery
		die();
	}



    /********************* templates, css, javascript *********************/


    private static function productionCSS() {
    ?>
         <style type="text/css">
        body{background:#e5e5e5;font-family:"Lucida Grande", verdana, arial, helvetica, sans-serif;font-size:12px;
        color:#333;margin:0 auto;padding:0;}#modal{width:610px;background:#FFF;border:10px solid #CCC;
        margin:28px auto 0;padding:6px 20px 0;}#modal h1{color:#C00;font-size:20px;margin:13px 0;}
        #modal p{line-height:16px;margin:12px 0;}#modal span{color:#666;}
        </style>
    <?php }

    private static function css() {
    ?>
         <style type="text/css">
        body{background:#fff;color:#33393c;font:12px/1.5 "Trebuchet MS", "Geneva CE", lucida,sans-serif;
        margin:0;}#title{background:#222;color:#565656;border-bottom:1px solid #C52F24;margin:0 auto;
        padding:10px 30px;}#message,.error{background-color:#C52F24;color:#fff;font-weight:700;
        font-size:100%;margin:0;padding:1px 0;}#message{border-top:1px solid #980905;padding:5px 30px 10px;}
        h1{margin-bottom:0;font-weight:400;font-size:175%;}#box{background-color:#EEE;border:1px solid #ADAEAF;
        margin:10px 30px 0;padding:5px;}#file{background-color:#D5E9F6;border:1px solid #8FCDF6;
        color:#234A69;margin:10px 30px 0;padding:5px;}.code{background-color:#FFF9D8;border:1px solid #FECA51;
        margin:10px 30px;padding:5px;}i{color:#999;}.num{color:#9E9E7E;font-style:normal;font-weight:400;}
        a{color:#980905;}table{font:16px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;font-size:100%;}
        td{padding-right:20px;}#title b,span.err{color:#FFF;}
        </style>
     <?php }

    public static function jsToggle() {
    ?>
        <script type="text/javascript">
        function toggle(id){var e=document.getElementById(id);
         if(e.style.display=='block')e.style.display='none';else e.style.display='block'}
       </script>
     <?php }


}