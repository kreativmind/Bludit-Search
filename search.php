<!DOCTYPE html>
<html lang="en">
<head>
	<title>Bludit Search</title>

<style>
.result{
	border: 1px solid black; 
	margin:10px 0px;
	padding: 10px;
}

.highlight{
	background-color: yellow;
}

.error{
	color:tomato;
}

body{
	max-width:800px;
	margin: 0 auto;	
	padding-top:20px;
}

ul,ol,li{
	list-style-type:none;
}
</style>
</head>

<body>

<?php
// Load time init
$loadTime = microtime(true);

// Security constant
define('BLUDIT', true);

// Directory separator
define('DS', DIRECTORY_SEPARATOR);

// PHP paths for init
define('PATH_ROOT', __DIR__.DS);
define('PATH_BOOT', PATH_ROOT.'bl-kernel'.DS.'boot'.DS);

// Init
require(PATH_BOOT.'init.php');

/* Nice Snippet Example | Source: http://stackoverflow.com/questions/8032312/find-specific-text-in-multiple-txt-files-in-php

$path_to_check = '';
$needle = 'match';

foreach(glob($path_to_check . '*.txt') as $filename)
{
  foreach(file($filename) as $fli=>$fl)
  {
    if(strpos($fl, $needle)!==false)
    {
      echo $filename . ' on line ' . ($fli+1) . ': ' . $fl;
    }
  }
}
*/

/*
NOTES: 
1. Warning: This searches through all the entries even the unpublished ones. Consider to use this only for private purposes.
2. Warning: Code is not audited for security vulnerabilities.
3. The same post/page result is returned if there is more than one instance of the search term found. toDo: Find a way to group them in the same <div>

Process followed to search through files:

1. Get the list of files from bl-content/posts and bl-content/pages
2. Additional post description, username, date, Tags, Cover Images cannot be searched since they are inserted in databases/posts.php or databases/pages.php
However, it does not matter since we are searching only the title and content of each entry.
3. So after we get a list of files, we use stripos() function to search and echo the $line number with the link.
4. Search might be heavy on system resources as the site becomes bigger but this is a very simple solution to search without Google or any other external services.
*/

// Initialise paths
$path_posts_dir = PATH_POSTS;
$path_pages_dir = PATH_PAGES;
// Initialise Search count
$count = 0;

// START Search function: 
// Example Usage: Posts: search(path, post, caseOption) 
// Example Usage: Pages: search(path, '', caseOption)
function search($pathToSearch, $type, $caseSensitivity){	
	// Variable scope fix
	global $count;
	global $Site;
	global $search_term;
	global $case;

	// Assign posts array list to variable and also Remove . and .. from array
	$posts_list = array_diff(scandir($pathToSearch), array('..', '.'));
	//print_r($posts_list); // Uncomment to troubleshoot

	foreach($posts_list as $post){
		$post_path = $pathToSearch . $post . "/index.txt" ;
		// echo $post; // Uncomment to troubleshoot
		// echo file_get_contents($post); // Uncomment to troubleshoot
		// Special characters are converted to HTML entities - htmlspecialchars() in displayed results($fl) to prevent the search from breaking the final output. Eg: if string: <head> is a search query, it breaks the page if not converted FIX: <head> becomes &lt;head&gt;  
		// Note: HTML tags can be searched(Example use-case: Code snippets) but are not highlighted.
		foreach(file($post_path) as $fli=>$fl){
			switch ($case) {
				case 'n':
				if(stripos($fl, $search_term)!==false){
					// echo $post . ' on line ' . ($fli+1) . ': ' . $fl . "<br>";
					echo "\n<div class='result'>";
					echo "<a href='" . $Site->url() . "$type"; if($type != ''){echo "/";} echo $post . "'>$post</a> <br>"; // If it is a post return "post/" else don't return anything.
					echo '<b>Line ' . ($fli+1) . ':</b> ' . str_ireplace($search_term, "<span class='highlight'>$search_term</span>", htmlspecialchars($fl)) . '</div>';
					$count++;
					} 
				break;
				
				case 'y': // If case-sensitive, use strpos() and str_replace() instead of stripos() and str_ireplace()
				if(strpos($fl, $search_term)!==false){
					// echo $post . ' on line ' . ($fli+1) . ': ' . $fl . "<br>";
					echo "\n<div class='result'>";
					echo "<a href='" . $Site->url() . "$type"; if($type != ''){echo "/";} echo $post . "'>$post</a> <br>"; // If it is a post return "post/" else don't return anything.
					echo '<b>Line ' . ($fli+1) . ':</b> ' . str_replace($search_term, "<span class='highlight'>$search_term</span>", htmlspecialchars($fl)) . '</div>';
					$count++;
					} 
				break;
			}
		}
	}
}
// END Search function

echo "
<form method='get' action='search.php'>
<p>Search
	<input type='radio' name='filter' value='all' id='all' checked><label for='all'>All</label>
	<input type='radio' name='filter' value='onlyPosts' id='onlyPosts'><label for='onlyPosts'>Only Posts</label>
	<input type='radio' name='filter' value='onlyPages' id='onlyPages'><label for='onlyPages'>Only Pages</label>
</p>
<p>Options: <label for='case'>Case-sensitive</label><input type='checkbox' name='case' id='case' value='y'></p>
<input type='text' name='searchQuery' />
<input type='submit' value='Search'></form>";

if (isset($_GET['searchQuery']) && $_GET['searchQuery'] != ''){
	// HTML tags are allowed to be searched. Consider strip_tags(). Is it needed?
	$search_term = $_GET['searchQuery'];	

	// Case error-handling
	if (isset($_GET['case']) && $_GET['case'] != ''){
		$tmp = $_GET['case'];
		if($tmp == 'y') {$case = $tmp;} 
		elseif($tmp == 'n') {$case = $tmp;} 
		else{echo "<br><span class='error'>ERROR:</span> Invalid case."; exit;}
	} else $case = 'n'; // Set case to 'n' if no option is passed.

	// Prevent XSS: htmlspecialchars()
	echo "<p>Search term: " . htmlspecialchars($search_term) . " [Case-sensitive: $case]</p>";

	// Call Search function according to choice
	$filter = $_GET['filter'];
	switch ($filter) {
		case 'all':
			search($path_posts_dir, "post", $case);
			search($path_pages_dir, "", $case);
			break;
	
		case 'onlyPosts':
			search($path_posts_dir, "post", $case);
			break;
	
		case 'onlyPages':
			search($path_pages_dir, "", $case);
			break;
			
		default:
			echo "<br><span class='error'>ERROR:</span> Invalid filter.";
			exit;
			break;
	}
	// Display count and wall clock time taken to find results.
	echo "<p>" . $count . " result(s) found. (" . round((microtime(true) - $loadTime), 2) . "seconds)</p>" ;
}
?>
</body>
</html>