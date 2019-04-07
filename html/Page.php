<?php

/**
 * Defines the LibEquit\Page base class.
 *
 * ### Dependencies
 * classes/equit/LibEquit\Application.php
 * classes/equit/AppLog.php
 * classes/equit/LibEquit\PageElement.php
 * classes/equit/PageSection.php
 *
 * ### Todo
 * - separate out AIO-specific code to AioPage and leave this as base class.
 *
 * ### Changes
 * - (2018-01) page sections main and navbar can be configured in/out.
 * - (2017-05) Updated documentation. Migrated to `[]` syntax for arrays. Changed access modifier
 *   for addSectionElement() to `protected` to allow access from reimplementing classes.
 * - (2013-12-10) First version of this file.
 *
 * @file LibEquit\Page.php
 * @author Darren Edale
 * @version 1.1.2
 * @package libequit
 * @date Jan 2018
 */

namespace Equit\Html;

require_once("includes/string.php");

use Equit\Application;
use Equit\AppLog;
use Equit\DataController;
use Equit\Html\Division;
use Equit\Html\PageElement;
use Equit\Translator;
use StdClass;

/**
 * Represents the application page being created.
 *
 * The application's UI is a web page. This class represents that page, and enables plugins and
 * other code to add content to the page in a way that retains the overall structure of the page.
 *
 * The page is divided up into logical sections. At present, three sections are available:
 * - the main navigation
 * - the main section
 * - the page-specific menu bar.
 *
 * The primary content on the page is contained in the main section. This includes the page-specific
 * menu bar at the top. Global options, usually links for the user to click that represent logical
 * top-level parts of the application, are presented in the main navigation.
 *
 * Content can be added to the page using addNavbarElement(), addMainElement() and
 * addMenubarElement() to add to the main navigation, main section and page-specific menu bar
 * respectively. Alternatively your code can call navbar(), mainSection() or menuBar() to retrieve
 * the respective section container and manipulate that directly.
 *
 * Scripts and stylesheets can also be added. In both cases, both URLs identifying the location of
 * the content and the literal content itself are accepted. To add URLs, use addScriptUrl() or
 * addStylesheetUrl(); for literal content use addJavascript() or addCss().
 *
 * Scripts are added to the page using `<script>` elements in the `<head>` section of the page. For
 * stylesheets, those provided using a URL are added using `<link>` elements in the `<head>` section
 * of the page; those provided as literal CSS are added using `<style>` elements also in the
 * `<head>` section of the page.
 *
 * For both scripts and stylesheets, a MIME type is required to be associated with the content when
 * added as an URL so that the user agent knows what to do with it. (For literal content,
 * `text/javascript` for scripts and `text/css` for stylesheets are assumed and sent as the MIME
 * type to the user agent.) The default MIME types for URLs are `text/css` for stylesheets and
 * `text/javascript` for scripts.
 *
 * @note
 * Literal CSS or javascript content will be escaped using htmlEntities() when the HTML for the page
 * is generated. There is no need to escape your styles or scripts for HTML and doing so might
 * introduce bugs in the page that are difficult to pin down.
 *
 * The class defines one static helper method, htmlEntities(), that can be used to escape any
 * content you want to add to the page. It assumes the input and output are UTF-8 encoded. Use this
 * from your plugins to escape any tag attributes or literal text content for the page.
 *
 * ### Actions
 * This module does not support any actions.
 *
 * ### API Functions
 * This module does not provide an API.
 *
 * ### Events
 * This module does not emit any events.
 *
 * ### Connections
 * This module does not connect to any events.
 *
 * ### Settings
 * The following settings are used in this module:
 *
 * - **page.head.content**
 *   _optional_
 *   `string`
 *
 *   The content for the page `<head>` section. This **must** be valid HTML for the `<head>` section
 *   of the targeted document type. It **must not** contain the opening and closing `<head></head>`
 *   tags.
 *
 *   If this setting is not specified, an empty `<head>` section will be used.
 *
 * - **page.body.head.content**
 *   _optional_
 *   `string`
 *
 *   The content for the page head (the top part of the page body). This **must** be valid HTML for
 *   the targeted document type. It **must not** contain the opening and closing `<body></body>`
 *   tags.
 *
 *   The setting supports language-specific values.
 *
 *   This setting is mutually exclusive with `page.body.head.file`. If both are specified,
 *   `page.body.head.content` takes precedence.
 *
 * - **page.body.head.file**
 *   _optional_
 *   `string`
 *
 *   The (relative) path to a file containing content for the page head (the top part of the page
 *   body). The content of the file **must** be valid HTML for the targeted document type. It
 *   **must not** contain the opening and closing `<body></body>` tags.
 *
 *   The setting supports language-specific values.
 *
 *   This setting is mutually exclusive with `page.body.head.content`. If both are specified,
 *   `page.body.head.content` takes precedence.
 *
 * - **page.body.tail.content**
 *   _optional_
 *   `string`
 *
 *   The content for the page tail (the bottom part of the page body). This **must** be valid HTML
 *   for the targeted document type. It **must not** contain the opening and closing `<body></body>`
 *   tags.
 *
 *   The setting supports language-specific values.
 *
 *   This setting is mutually exclusive with `page.body.tail.file`. If both are specified,
 *   `page.body.tail.content` takes precedence.
 *
 * - **page.body.tail.file**
 *   _optional_
 *   `string`
 *
 *   The (relative) path to a file containing content for the page tail (the bottom part of the page
 *   body). The content of the file **must** be valid HTML for the targeted document type. It **must
 *   not** contain the opening and closing `<body></body>` tags.
 *
 *   The setting supports language-specific values.
 *
 *   This setting is mutually exclusive with `page.body.tail.content`. If both are specified,
 *   `page.body.tail.content` takes precedence.
 *
 * - **page.navbar.enabled**
 *   _optional_
 *   `bool`
 *
 *   Whether or not the navbar part of the page is enabled. The default is _true_.
 *
 * - **page.main.enabled**
 *   _optional_
 *   `bool`
 *
 *   Whether or not the main content part of the page is enabled. The default is _true_.
 *
 * - **page.menubar.enabled**
 *   _optional_
 *   `bool`
 *
 *   Whether or not the page-specific menubar part of the page is enabled. The default is _true_.
 *
 * @note This setting is not currently honoured. The menubar is embedded in the main section, and
 *   if the main section is enabled, the page-specific menu bar is enabled automatically.
 *
 * ### Session Data
 * This module does not create a session context.
 *
 * @class Page
 * @author Darren Edale
 * @package libequit
 *
 * @actions _None_
 * @aio-api _None_
 * @events _None_
 * @connections _None_
 * @settings page.head.content page.body.head.content page.body.head.file page.body.tail.content page.body.tail.file
 * page.navbar.enabled page.main.enabled page.menubar.enabled
 * @session _None_
 */
class Page {
	/** Script flag for no special treatment. */
	const NoScriptFlags = 0x00;

	/** Script flag to defer loading of the script. */
	const DeferScript = 0x01;

	/** Script flag to loading the script asynchronously. */
	const AsyncScript = 0x02;

	/** Default script flags. */
	const DefaultScriptFlags = self::NoScriptFlags;

	/** Internal type identifier for scripts that are URLs. */
	const ScriptTypeUrl = 1;

	/** Internal type identifier for scripts that are literal js source code. */
	const ScriptTypeJsSource = 2;

	/** Internal type identifier for stylesheets that are URLs. */
	const SheetTypeUrl = 1;

	/** Internal type identifier for stylesheets that are literal css source. */
	const SheetTypeCssSource = 2;

	/** The page sections. */
	private $m_sections = [];

	/** The stylesheets added to the page. */
	private $m_stylesheets = [];

	/** The scripts added to the page. */
	private $m_scripts = [];

	/**
	 * Create a new LibEquit\Page object.
	 */
	public function __construct() {
		$uid                         = @constant("app.uid");
		$this->m_sections["main"]    = new Division("$uid-main");
		$this->m_sections["menubar"] = new Division("$uid-menubar");
		$this->m_sections["navbar"]  = new Division("$uid-navbar");
		$this->m_sections["main"]->addChild($this->m_sections["menubar"]);
	}

	/**
	 * Add an element to a section.
	 *
	 * @param $section `string` The name of the section to add to.
	 * @param $e PageElement The element to add.
	 *
	 * This is an internal helper method that is used when adding content to any known page section.
	 * It helps keep the class futureproof by making it easy to add or remove sections in future.
	 *
	 * @return bool _true_ if the element was added to the section, _false_
	 * otherwise.
	 */
	protected function addSectionElement($section, $e) {
		if(!($e instanceof PageElement)) {
			AppLog::error("invalid page element", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		return $this->m_sections[$section]->addChild($e);
	}

	/**
	 * Fetch the main page section.
	 *
	 * @return Division The main page section.
	 */
	public function mainSection(): Division {
		return $this->m_sections["main"];
	}

	/**
	 * Fetch the menubar page section.
	 *
	 * @return Division The menubar page section.
	 */
	public function menuBar() {
		return $this->m_sections["menubar"];
	}

	/**
	 * Fetch the navbar page section.
	 *
	 * @return Division The navbar page section.
	 */
	public function navbar() {
		return $this->m_sections["navbar"];
	}

	/**
	 * Add an element to the main page section.
	 *
	 * @param $element PageElement The element to add to the main section.
	 *
	 * The element is appended to the existing content in the main section. If you need to insert
	 * elements elsewhere (which is not recommended), you should call mainSection() and manipulate
	 * the PageSection directly.
	 *
	 * @return bool _true_ if the element was added, _false_ otherwise.
	 */
	public function addMainElement($element) {
		return $this->addSectionElement("main", $element);
	}

	/**
	 * Add an element to the menubar page section.
	 *
	 * @param $element PageElement The element to add to the menubar section.
	 *
	 * The element is appended to the existing content in the menubar section. If you need to insert
	 * elements elsewhere (which is not recommended), you should call menuBar() and manipulate the
	 * PageSection directly. It is recommended that you add only simple content such as hyperlinks
	 * to the menubar. Content that takes up too much space will quickly make the menubar unwieldy
	 * for the end user.
	 *
	 * @return bool _true_ if the element was added, _false_ otherwise.
	 */
	public function addMenubarElement($element) {
		return $this->addSectionElement("menubar", $element);
	}

	/**
	 * Add an element to the navbar page section.
	 *
	 * @param $element PageElement The element to add to the navbar section.
	 *
	 * The element is appended to the existing content in the navbar section. If you need to insert
	 * elements elsewhere (which is not recommended), you should call menuBar() and manipulate the
	 * PageSection directly. It is recommended that you add only simple content such as hyperlinks
	 * to the navbar. Content that takes up too much space will quickly make the navbar unwieldy for
	 * the end user.
	 *
	 * @return bool _true_ if the element was added, _false_ otherwise.
	 */
	public function addNavbarElement($element) {
		return $this->addSectionElement("navbar", $element);
	}

	/**
	 * Add the URL for a stylesheet to the page.
	 *
	 * @param $url `string` The URL for the stylesheet.
	 * @param $mimetype string _optional_ The MIME type of the content.
	 *
	 * If the MIME type parameter is not specified, the default MIME type of text/css will be used.
	 * If you provide a MIME type, it is not validated - as long as it is a string, it will be
	 * accepted. Both the URL and the MIME type will be escaped for HTML when the page is generated.
	 *
	 * The stylesheet will be added using a `<link>` element in the page's `<head>` section with the
	 * `rel` attribute set to `stylesheet`.
	 *
	 * @return bool _true_ if the stylesheet URL was added to the page, _false_ otherwise.
	 */
	public function addStylesheetUrl($url, $mimetype = "text/css") {
		if(!is_string($url)) {
			AppLog::error("invalid stylesheet URL", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		if(!is_string($mimetype)) {
			AppLog::error("invalid stylesheet mimetype", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		$sheet                 = new StdClass;
		$sheet->type           = self::SheetTypeUrl;
		$sheet->url            = $url;
		$sheet->mimetype       = $mimetype;
		$this->m_stylesheets[] = $sheet;

		return true;
	}

	/**
	 * Add CSS to the page.
	 *
	 * @param $css `string` The CSS source to add to the page.
	 *
	 * The CSS will be added using a `<style>` element, with the content between the opening and
	 * closing tags, in the page's `<head>` section. The element's `type` attribute will be set to
	 * `text/css`.
	 *
	 * The CSS content will automatically be escaped for HTML when the page is generated.
	 *
	 * @return bool _true_ if the CSS was added to the page, _false_ otherwise.
	 */
	public function addCss($css) {
		if(!is_string($css)) {
			AppLog::error("invalid CSS", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		$sheet                 = new StdClass;
		$sheet->type           = self::SheetTypeCssSource;
		$sheet->css            = $css;
		$this->m_stylesheets[] = $sheet;

		return true;
	}

	/**
	 * Add the URL for a script to the page.
	 *
	 * @param $url `string` The URL for the script.
	 * @param $mimeType string _optional_ The MIME type of the content.
	 * @param $flags int _optional_ Flags defining how the script should be handled by
	 * the user agent.
	 *
	 * If the MIME type parameter is not specified, the default MIME type of text/javascript will be
	 * used. If you provide a MIME type, it is not validated - as long as it is a string, it will be
	 * accepted. Both the URL and the MIME type will be escaped for HTML when the page is generated.
	 *
	 * The script will be added using a `<script>` element with its `src` attribute set to the URL
	 * in the page's `<head>` section.
	 *
	 * The $flags parameter enables you to provide some indication of how the user agent should
	 * handle the script. At present, there is one flag available, `DeferScript`, which will cause
	 * the `<script>` element to have its `defer` property set. By default, this flag is set; if you
	 * don't want the flag set, pass `NoScriptFlags` for the flags.
	 *
	 * @return bool _true_ if the script URL was added to the page, _false_ otherwise.
	 */
	public function addScriptUrl($url, $mimeType = "text/javascript", $flags = self::DefaultScriptFlags) {
		if(!is_string($url)) {
			AppLog::error("invalid script URL", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		if(!is_string($mimeType)) {
			AppLog::error("invalid script MIME type", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		if(!is_int($flags)) {
			$flags = self::DefaultScriptFlags;
		}

		$this->m_scripts[] = (object) [
			"type" => self::ScriptTypeUrl,
			"url" => $url,
			"mimetype" => $mimeType,
			"flags" => $flags,
		];

		return true;
	}

	/**
	 * Add javascript to the page.
	 *
	 * @param $src `string` The javascript source code to add to the page.
	 * @param $flags int _optional_ Flags defining how the script should be handled by the user
	 * agent.
	 *
	 * The javascript will be added using a `<script>` element, with the content between the opening
	 * and closing tags, in the page's `<head>` section. The element's `type` attribute will be set
	 * to `text/javascript`.
	 *
	 * The `$flags` parameter enables you to provide some indication of how the user agent should
	 * handle the javascript. At present, there is one flag available, `DeferScript`, which will
	 * cause the `<script>` element to have its `defer` property set. By default, this flag is set;
	 * if you don't want the flag set, pass `NoScriptFlags` for the flags.
	 *
	 * The source will be escaped for HTML when the page is generated.
	 *
	 * @return bool _true_ if the javascript was added to the page, _false_ otherwise.
	 */
	public function addJavascript($src, $flags = self::DefaultScriptFlags) {
		if(!is_string($src)) {
			AppLog::error("invalid script URL", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		if(!is_int($flags)) {
			$flags = self::DefaultScriptFlags;
		}

		$script            = new StdClass;
		$script->type      = self::ScriptTypeJsSource;
		$script->src       = $src;
		$script->flags     = $flags;
		$this->m_scripts[] = $script;

		return true;
	}

	/**
	 * Provides the core `<head>` content for the page template.
	 *
	 * This base implementation provides content specified in the `page.head.content` setting. You
	 * can alter this behaviour by reimplementing this method your subclasses.
	 *
	 * The html() method adds user-supplied scripts and stylesheets to this content. The content
	 * provided by this method **must not** include the `<head>` or `</head>` tags.
	 *
	 * @return string The core head content for the page template.
	 */
	protected function templateHeadContent() {
		static $s_head = null;

		if(is_null($s_head)) {
			$db = Application::instance()->dataController();

			if($db instanceof DataController) {
				$s_head = (string) $db->setting("page.head.content", "");
			}
			else {
				$s_head = "";
			}
		}

		return "<!-- head content supplied by LibEquit\Page class-->$s_head";
	}


	/**
	 * Provides the core content for the top part of the page template.
	 *
	 * This base implementation provides content specified in the `page.body.head.content` or
	 * `page.body.head.file` setting. Both of these settings support language-specific localisation.
	 * If both are provided, the content setting is preferred over the file setting.
	 *
	 * You can alter the behaviour of this method by reimplementing it in your subclasses. The
	 * content provided by this method **must not** include the `<body>` or `</body>` tags.
	 *
	 * @return string The core content for the top part of the page template.
	 */
	protected function templateBodyHeadContent() {
		static $s_bodyHead = null;

		if(is_null($s_bodyHead)) {
			$app = Application::instance();
			$db  = $app->dataController();

			if($db instanceof dataController) {
				$lang = null;
				$t    = $app->translator();

				if($t instanceof Translator) {
					$lang = $t->language();
				}

				if(!empty($lang)) {
					$s_bodyHead = $db->setting("page.body.head.content.$lang");
				}

				if(empty($s_bodyHead)) {
					$s_bodyHead = $db->setting("page.body.head.content");
				}

				if(empty($s_bodyHead)) {
					if(!empty($lang)) {
						$s_bodyHead = $db->setting("page.body.head.file.$lang");
					}

					if(empty($s_bodyHead)) {
						$s_bodyHead = $db->setting("page.body.head.file");
					}
				}
			}

			if(empty($s_bodyHead)) {
				$s_bodyHead = "<header><p>" . html($app->title()) . "</p></header><section id=\"app-main-container\">";
			}
		}

		return $s_bodyHead;
	}


	/**
	 * Provides the core content for the bottom part of the page
	 * template.
	 *
	 * This base implementation provides content specified in the `page.body.tail.content` or
	 * `page.body.tail.file` setting. Both of these settings support language-specific localisation.
	 * If both are provided, the content setting is preferred over the file setting.
	 *
	 * You can alter the behaviour of this method by reimplementing it in your subclasses. The
	 * content provided by this method **must not** include the `<body>` or `</body>` tags.
	 *
	 * @return string The core content for the bottom part of the page template.
	 */
	protected function templateBodyTailContent() {
		static $s_bodyTail = null;

		if(is_null($s_bodyTail)) {
			$app = Application::instance();
			$db  = $app->dataController();

			if($db instanceof dataController) {
				$lang = null;
				$t    = $app->translator();

				if($t instanceof Translator) {
					$lang = $t->language();
				}

				if(!empty($lang)) {
					$s_bodyTail = $db->setting("page.body.tail.content." . $lang);
				}

				if(empty($s_bodyTail)) {
					$s_bodyTail = $db->setting("page.body.tail.content");
				}

				if(empty($s_bodyTail)) {
					if(!empty($lang)) {
						$s_bodyTail = $db->setting("page.body.tail.file." . $lang);
					}

					if(empty($s_bodyTail)) {
						$s_bodyTail = $db->setting("page.body.tail.file");
					}
				}
			}

			if(empty($s_bodyTail)) {
				$s_bodyTail = "</section><footer></footer>";
			}
		}

		return $s_bodyTail;
	}

	/**
	 * Generate the page's HTML.
	 *
	 * The HTML generated is XHTML1.0 Strict compliant, encoded as UTF-8. The one exception to this is any HtmlLiteral
	 * objects that are in the document tree. These output their content verbatim, and therefore it will only be
	 * compliant if all the HtmlLiteral objects were provided with compliant content.
	 *
	 * @return string The HTML for the page.
	 */
	public function html(): string {
		$app           = Application::instance();
		$db            = $app->dataController();
		$doNavbar      = $db->setting("page.navbar.enabled", true);
		$doMainSection = $db->setting("page.main.enabled", true);
// 		$doMenuBar = $db->setting("page.menubar.enabled", true);

		$head = "<!DOCTYPE html>\n<html><head><title>" . html($app->title()) . "</title>" . $this->templateHeadContent();
		$body = $this->templateBodyHeadContent() .
			($doMainSection ? chr(10) . $this->m_sections["main"]->html() : "") .
			($doNavbar ? chr(10) . $this->m_sections["navbar"]->html() : "") .
			$this->templateBodyTailContent() .
			"\n</body></html>";

		/* now all the main content has been generated, add the stylesheets and
		 * scripts to the head section. this is done afterwards so that any
		 * stylesheets or scripts that are added to the page only while page
		 * element HTML is being generated are not missed out */
		$seenUrls = [];

		foreach($this->m_stylesheets as $sheet) {
			switch($sheet->type) {
				case self::SheetTypeUrl:
					if(in_array($sheet->url, $seenUrls)) {
						AppLog::warning("ignoring duplicate stylesheet URL \"{$sheet->url}\"", __FILE__, __LINE__, __FUNCTION__);
						break;
					}

					$head       .= "<link rel=\"stylesheet\" type=\"" . html($sheet->mimetype) . "\" href=\"" . html($sheet->url) . "\" />\n";
					$seenUrls[] = $sheet->url;
					break;

				case self::SheetTypeCssSource:
					$head .= "<style type=\"text/css\">\n" . html($sheet->css) . "\n</style>\n";
					break;
			}
		}

		$seenUrls = [];

		foreach($this->m_scripts as $script) {
			$attrs = ($script->flags & self::DeferScript ? " defer=\"defer\"" : "");
			$attrs .= ($script->flags & self::AsyncScript ? " async=\"async\"" : "");

			switch($script->type) {
				case self::ScriptTypeUrl:
					if(in_array($script->url, $seenUrls)) {
						AppLog::warning("ignoring duplicate script URL \"{$script->url}\"", __FILE__, __LINE__, __FUNCTION__);
						break;
					}

					$head       .= "<script type=\"" . html($script->mimetype) . "\" src=\"" . html($script->url) . "\"$attrs></script>\n";
					$seenUrls[] = $script->url;
					break;

				case self::ScriptTypeJsSource:
					$head .= "<script type=\"text/javascript\"$attrs>\n{$script->src}\n</script>\n";
					break;
			}
		}

		return "$head</head><body>$body";
	}

	/**
	 * Output the page.
	 *
	 * The page is output to the current output stream. This is usually standard output which is usually what is sent to
	 * the user agent.
	 */
	public function output() {
		echo $this->html();
	}
}
