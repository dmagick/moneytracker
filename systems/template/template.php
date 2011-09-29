<?php

/**
 * Handles all things to do with templates.
 * Templates are used for:
 * creating (and then saving) a new post.
 */
class Template
{
	/**
	 * A list of templates for this system to serve up
	 * when the occasion arises.
	 */
	private static $_templateStack = array();

	/**
	 * An array of keywords per template to process.
	 */
	private static $_keywords = array();

	/**
	 * Where to get template files from.
	 *
	 * @var string
	 * @see setTemplateDir
	 */
	private static $_templateDir = NULL;


	/**
	 * Set the directory where to get templates from.
	 * This is generally done at the top of the main index script.
	 * Does a basic check to make sure the dir exists, and if
	 * it doesn't it will throw an exception.
	 *
	 * @param string $dir The template dir to use.
	 *
	 * @see _templateDir
	 *
	 * @return void
	 */
	public static function setDir($dir)
	{
		if (is_dir($dir) === FALSE) {
			throw new Exception("Template dir doesn't exist");
		}

		self::$_templateDir = $dir;
	}

	/**
	 * Get the current template directory.
	 *
	 * @see _templateDir
	 */
	public static function getDir()
	{
		return self::$_templateDir;
	}


	/**
         * Gets a template to be processed. This will be ready made html
         * but with some basic placeholders.
         * All this function does is return the template to the caller.
         * If it doesn't exist, an exception is thrown.
         *
         * @param string $templateName The template name you're looking for.
         *
         * @return string
 	 */
	public static function getTemplate($templateName=NULL)
	{
		$file = self::$_templateDir.'/'.$templateName.'.tpl';
		if (is_file($file) === FALSE) {
			throw new Exception("Template ".$templateName." doesn't exist");
		}

		$contents = file_get_contents($file);
		return $contents;
	}

	/**
	 * Put a template to display on the stack.
	 * We don't actually serve it yet in case a page does a redirect
	 * or something like that, we just store it.
	 * display() goes through the stack and does all the work.
	 */
	public static function serveTemplate($templateName=NULL)
	{
		self::$_templateStack[] = $templateName;
	}

	/**
	 * Process template actions. These are found in templates in the
	 * form of a keyword.
	 * ~template::action::item~
	 * eg to include another template, you do
	 * ~template::include::otherTemplateName~
	 * and otherTemplateName is processed as a normal template including
	 * keywords and possibly recursion.
	 * Returns the new content from processing the other template.
	 * Throws an exception for unknown action.
	 *
	 * @param string $action Template action to perform.
	 *
	 * @return string
	 */
	private static function processTemplateAction($action)
	{
		list($action, $item) = explode('::', $action);
		switch ($action) {
			case 'include':
				$content = self::getTemplate($item);
				$content = self::processKeywords($content, $item);
			break;

			default:
				throw new Exception("Unknown template action ".$action);
		}

		return $content;

	}

	/**
	 * Process keywords for a template if there are any to be processed.
	 * Returns the content with keywords replaced.
	 *
	 * @param string $content The content to put the keywords into.
	 * @param string $templateName The name of the template so we know which
	 *				keywords to get.
	 *
	 * @return string
	 */
	private static function processKeywords($content, $templateName)
	{
		preg_match_all('/~template::(.*?)~/', $content, $matches);
		if (empty($matches[1]) === FALSE) {
			foreach ($matches[1] as $mpos => $match) {
				$result = self::processTemplateAction($match);
				$content = str_replace($matches[0][$mpos], $result, $content);
			}
		}

        if (isset(self::$_keywords[$templateName]) === FALSE) {
            return $content;
        }

		$keywords = array_keys(self::$_keywords[$templateName]);
		$values   = array_values(self::$_keywords[$templateName]);
		$content  = str_replace($keywords, $values, $content);
		unset(self::$_keywords[$templateName]);
        return $content;
	}

	/**
	 * Replace built in keywords.
	 * For example, replace url keyword.
	 * Returns the content with keywords replced.
	 *
	 * @param string $content The content to process keywords for.
	 *
	 * @return string
	 */
	private static function processBuiltInKeywords($t, $content)
	{

		$source  = array(
                    '~url::baseurl~',
                   );
		$replace = array(
                    url::getUrl(),
                   );
        
        if (strpos($content, '~flashmessage~') !== FALSE) {
            $allMessages = '';
            $flashMessages = session::getFlashMessages();
            foreach ($flashMessages as $messageInfo) {
                $message     = $messageInfo[0];
                $messageType = $messageInfo[1];
                switch ($messageType)
                {
                    case 'error':
                        $templateName = 'flash.message.error';
                    break;
                    case 'success':
                        $templateName = 'flash.message.success';
                    break;
                }
                $template = self::getTemplate($templateName);
                $template = str_replace('~message~', $message, $template);
                $allMessages .= $template;
            }
            // Make sure we replace keywords in our messages as well,
            // before we add the flashmessage to the replacement list.
            $allMessages = str_replace($source, $replace, $allMessages);

            $source[]  = '~flashmessage~';
            $replace[] = $allMessages;
        }
		$content = str_replace($source, $replace, $content);
		return $content;
	}

	/**
	 * Go through the list of templates we've been told to process,
	 * fix up keywords and print the template out.
	 * This should be the last step of a page, so we go through the
	 * list of templates previously set, process keywords
	 * and print them out.
	 *
	 * @uses getTemplate
	 * @uses processKeywords
	 * @see _templateStack
	 */
	public static function display()
	{
		foreach (self::$_templateStack as $template) {
			$content = self::getTemplate($template);
			$content = self::processKeywords($content, $template);
            $content = self::processBuiltInKeywords($template, $content);
            echo $content;
		}
		self::$_templateStack = array();
	}

	/**
	 * Set a keyword and value for a particular template.
	 * This is used by processKeywords to go through and replace.
	 *
	 * @param string $template Template we are setting the keyword for.
	 * @param string $keyword  Keyword name.
	 * @param string $value    Keyword value.
	 *
	 * @uses _keywords
	 * @see processKeywords
	 */
	public function setKeyword($template, $keyword, $value)
	{
		if (isset(self::$_keywords[$template]) === FALSE) {
			self::$_keywords[$template] = array();
		}
		self::$_keywords[$template]['~'.$keyword.'~'] = $value;
	}

	public static function getKeyword($template, $keyword)
	{
		if (isset(self::$_keywords[$template]) === FALSE) {
			return NULL;
		}
		if (isset(self::$_keywords[$template]['~'.$keyword.'~']) === FALSE) {
			return NULL;
		}

		return self::$_keywords[$template]['~'.$keyword.'~'];
	}
}

/* vim: set expandtab ts=4 sw=4: */
