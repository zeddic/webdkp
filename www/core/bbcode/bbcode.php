<?php
/*===========================================================
bbcode()
A static utility class that can be called upon to parse
strings of their phpbbcodes and conver it into html
============================================================*/
include_once("util/fileutil.php");
include_once("general/image.php");
class bbcode{
	/*===========================================================
	Helper function
	Parses the given string of bb codes and replaces them
	with html
	============================================================*/
	function parseBBCode($string){

		$parser = $_GLOBALS["Framework_BBCodeParser"];
		if($parser == "") {
			$parser = bbcode::setupBBCodeParser();
		}

		$new_text = $parser->parse($string);

		return $new_text;
	}

	/*===========================================================
	Helper function
	Sets up a phpbb code parser and caches for the rest of this page load
	============================================================*/
	function setupBBCodeParser(){
		include_once("core/bbcode/stringparser_bbcode.class.php");
		$parser = new StringParser_BBCode();

		$parser->addParser (array ('block', 'inline', 'link', 'listitem'), 'htmlspecialchars');
		$parser->addParser (array ('block', 'inline', 'link' ), 'nl2br');

		$parser->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('h1', 'simple_replace', null, array ('start_tag' => '<h1 class="bbheader">', 'end_tag' => '</h1>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('h2', 'simple_replace', null, array ('start_tag' => '<h2 class="bbheader">', 'end_tag' => '</h2>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('h3', 'simple_replace', null, array ('start_tag' => '<h3 class="bbheader">', 'end_tag' => '</h3>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('h4', 'simple_replace', null, array ('start_tag' => '<h4 class="bbheader">', 'end_tag' => '</h4>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span style="text-decoration: underline;">', 'end_tag' => '</span>'),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('url', 'usecontent?', 'parseBBCodeUrl', array ('usecontent_param' => 'default'),
		                  'link', array ('listitem', 'block', 'inline'), array ('link'));
		$parser->addCode ('link', 'callback_replace_single', 'util::parseBBCodeUrl', array (),
		                  'link', array ('listitem', 'block', 'inline'), array ('link'));
		$parser->addCode ('img', 'usecontent', 'parseBBCodeImg', array (),
		                  'image', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('size', 'callback_replace', 'parseBBCodeSize', array (),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('color', 'callback_replace', 'parseBBCodeColor', array (),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('align', 'callback_replace', 'parseBBCodeAlign', array (),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('code', 'usecontent', 'parseBBCodeCode', array (),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('quote', 'callback_replace', 'parseBBCodeQuote', array (),
		                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
		$parser->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
                  'list', array ('block', 'listitem'), array ());
		$parser->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
		                  'listitem', array ('list'), array ());
		$parser->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
		$parser->setCodeFlag ('*', 'paragraphs', true);
		$parser->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
		$parser->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
		$parser->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
		$parser->setOccurrenceType ('img', 'image');

		$_GLOBALS["Framework_BBCodeParser"] = $parser;
		return $parser;
	}

	/*===========================================================
	Utility / helper method for the bbcode parser
	Remove everything but the newline charachter
	============================================================*/
	function bbcode_stripcontents ($text) {
	    return preg_replace ("/[^\n]/", '', $text);
	}


	/*===========================================================
	Alternative implementation. Still need to take some of these
	and convert it into a form for the above parser. The above
	parser class is much more powerful and has better error checking
	============================================================*/
	/*function parseBBCode($str){

	    $str = htmlentities($str);

	    $simple_search = array(
	                '/\[b\](.*?)\[\/b\]/is',
	                '/\[i\](.*?)\[\/i\]/is',
	                '/\[u\](.*?)\[\/u\]/is',
	                '/\[url\=(.*?)\](.*?)\[\/url\]/is',
	                '/\[url\](.*?)\[\/url\]/is',
	                '/\[align\=(left|center|right)\](.*?)\[\/align\]/is',
	                '/\[img\](.*?)\[\/img\]/is',
	                '/\[mail\=(.*?)\](.*?)\[\/mail\]/is',
	                '/\[mail\](.*?)\[\/mail\]/is',
	                '/\[font\=(.*?)\](.*?)\[\/font\]/is',
	                '/\[size\=(.*?)\](.*?)\[\/size\]/is',
	                '/\[color\=(.*?)\](.*?)\[\/color\]/is',
	                );

	    $simple_replace = array(
	                '<strong>$1</strong>',
	                '<em>$1</em>',
	                '<u>$1</u>',
	                '<a href="$1">$2</a>',
	                '<a href="$1">$1</a>',
	                '<div style="text-align: $1;">$2</div>',
	                '<img src="$1" />',
	                '<a href="mailto:$1">$2</a>',
	                '<a href="mailto:$1">$1</a>',
	                '<span style="font-family: $1;">$2</span>',
	                '<span style="font-size: $1;">$2</span>',
	                '<span style="color: $1;">$2</span>',
	                );

	    // Do simple BBCode's
	    $str = preg_replace ($simple_search, $simple_replace, $str);

	    // Do <blockquote> BBCode
	    $str = util::parseBBCodeQuote ($str);

	    return $str;
	} */



	/*function parseBBCodeQuote ($str) {
	    $open = '<blockquote>';
	    $close = '</blockquote>';

	    // How often is the open tag?
	    preg_match_all ('/\[quote\]/i', $str, $matches);
	    $opentags = count($matches['0']);

	    // How often is the close tag?
	    preg_match_all ('/\[\/quote\]/i', $str, $matches);
	    $closetags = count($matches['0']);

	    // Check how many tags have been unclosed
	    // And add the unclosing tag at the end of the message
	    $unclosed = $opentags - $closetags;
	    for ($i = 0; $i < $unclosed; $i++) {
	        $str .= '</blockquote>';
	    }

	    // Do replacement
	    $str = str_replace ('[' . 'quote]', $open, $str);
	    $str = str_replace ('[/' . 'quote]', $close, $str);

	    return $str;
	} */

}


/*===========================================================
callback function
Callback from the parser class. Formats a image tag.
Must be global to be called back :(
If image tag is in the form [img id='5' size='m'][/img] it will
display the image from the image database with the given id
============================================================*/
function parseBBCodeImg ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }

	$alt = $attributes["alt"];

	$toReturn = "";

	if($attributes["border"] != "")
		$borderTag = " class = 'borderedImage' ";
	else
		$borderTag = "";

	//if an id tag is present, this image is one from the database and not
	//a a direct url
    if($attributes["id"] != "") {
		$id = stripslashes($attributes["id"]);
		$id = str_replace("'","",$id);

		$image = & new image();
		$image->loadFromDatabase($id);

		//only enter the link if the id was valid
		if($image->id != "") {

			$size = stripslashes($attributes["size"]);
			$size = str_replace("'","",$size);

			$path = $image->getSizePath($size);

			/*if($size == "")
				$size = "m";

			if($size == "l")
				$path = $image->getLarge();
			else if($size == "o")
				$path = $image->getOriginal();
			else if($size == "m")
				$path = $image->getMedium();
			else if($size == "s")
				$path = $image->getSmall();
			else if($size == "sq")
				$path = $image->getSquare();
			else if($size == "thumbnail")
				$path = $image->getThumbnail();
			else if($size == "t")
				$path = $image->getThumbnail();

			if($path == "")
				return "";*/

			$toReturn =  '<img src="'.$path.'" alt="'.$alt.'" '.$borderTag.' >';
			//add lightbox affect if requested
			if($attributes["lightbox"]) {
				$toReturn = "<a href='".$image->getSizePath($attributes["lightbox"])."' rel='lightbox' title='$image->comment'>".$toReturn."</a>";
			}
			return $toReturn;
		}
		else
			return  "";

	}
	else if($content != "") {
		$toReturn =  '<img src="'.$content.'" alt="'.$alt.'" '.$borderTag.' >';
	}

	return '<img src="'.htmlspecialchars($content).'" alt="'.$alt.'" '.$borderTag.' >';
}
/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeUrl ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }
    if (!isset ($attributes['default'])) {
        return '<a href="'.htmlspecialchars ($content).'">'.htmlspecialchars ($content).'</a>';
    }
    return '<a href="'.htmlspecialchars ($attributes['default']).'">'.$content.'</a>';
}

/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeSize ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }

    if(isset($attributes['default'])) {
    	$size = $attributes['default'];
		return "<span style='font-size:".$size."pt'>$content</span>";
	}

	return $content;
}

/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeColor ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }

    if(isset($attributes['default'])) {
    	$color = $attributes['default'];
		return "<span style='color:".$color."'>$content</span>";
	}

	return $content;
}

/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeAlign ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }

    if(isset($attributes['default'])) {
    	$align = $attributes['default'];
		return "<div style='text-align:".$align."'>$content</div>";
	}

	return $content;
}

/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeCode ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }
	$content = str_replace("<br />","",$content);
    $content = htmlspecialchars_decode($content);
	$content = highlight_string($content, TRUE);

	return "<div class='code'><pre>$content</pre></div>";
}

/*===========================================================
callback function
Callback from the parser class. Formats a url tag.
Must be global to be called back :(
============================================================*/
function parseBBCodeQuote ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        return true;
    }

    return "<blockquote><div>$content</div></blockquote>";
}
