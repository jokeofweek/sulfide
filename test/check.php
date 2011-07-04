<?php

/**
 * Check for existance of submodule as well as its initialization
 */
if (!is_dir('simpletest') || !file_exists('simpletest/autorun.php')) {
	$file = $_SERVER['PHP_SELF'];
	echo <<<CONTENT
	<html>
		<head><title>Sulfide Tests - Error</title></head>
		<body>
			<p>Sulfide could not run the tests as the simpletest library was not found in the /test folder.</p>
			<p>Did you <span style="font-weight: bold;">clone Sulfide from Github</span>? If so, you must update the simpletest submodule. This can be done with the following commands
				<pre>git submodule init</br>git submodule update</br></pre>
			</p>
			<p>Did you <span style="font-weight: bold;">download and extract Sulfide from an archive file</span>? If so, you must download the library, which is available <a href="http://www.simpletest.org/en/download.html">here</a> 
			and extract the simpletest folder into the /test folder. To check whether the files were extracted properly:
			<ul>
				<li>Starting at the root directory of the sulfide application (where /core and /test folders are), go to the test folder</li>
				<li>From here, go to the simpletest folder</li>
				<li>In this folder, you should see a list of directories and files, including a file called 'autorun.php'</li>
			</ul>
			If you can see this file, you have extracted simpletest properly.
			</p>
			<p>Once you have installed simpletest, simply <a href="{$file}">refresh</a> this page.</p>
		</body>
	</html>
CONTENT;
	die();
}
