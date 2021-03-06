<?php
/**
 * @file InlineTextEdit.php
 * @author darren
 * @date May 2019
 * @version
 */

namespace Equit\Html;

use Equit\AppLog;

/**
 * A text edit widget that provides on-demand inline editing.
 *
 * This is for use in cases where normal text needs to be displayed until the user wants to edit it, when it becomes
 * a text editor. The usual trigger for the user wanted to edit it is when s/he clicks on the text. When the user has
 * finished editing, the modified content can either just be displayed or can be submitted using an API call.
 *
 * The API function is called using the _Application.doApiCall()_ javascript method, and should provide a standard API
 * response. If the response code is 0, the user's input is accepted; otherwise it is rejected and the original value
 * is restored.
 * response data is expected to be a list of suggestions separated by linefeeds
 * (i.e. one suggestion per line). It should accept one argument which will be provided as an URL parameter, and whose
 * value will be the content of the text input box. The name can be set to whatever named URL parameter the API function
 * is expecting. It should be set with the _setAutocompleteApiFunction()_ method.
 *
 * Instances of this class can only use the single-line types, and cannot use the _Password_ type.
 *
 * Objects of this class depend on runtime javascript code which must be included in the HTML page for any page that
 * uses one. The URL for this script is provided by the _runtimeScriptUrl()_ method. You should add this to your page,
 * using _Page::addScriptUrl()_ (assuming you are using the built-in LibEquit\Page class to build the HTML page).
 *
 * ### Actions
 * This module does not support any actions.
 *
 * ### API Functions
 * This module does not provide any AIO API functions.
 *
 * ### Events
 * This module does not emit any events.
 *
 * ### Connections
 * This module does not connect to any events.
 *
 * ### Settings
 * This module does not read any settings.
 *
 * ### Session Data
 * This module does not create a session context.
 *
 * @actions _None_
 * @aio-api _None_
 * @events _None_
 * @connections _None_
 * @settings _None_
 * @session _None_
 *
 * @class AutocompleteTextEdit
 * @author Darren Edale
 * @package libequit
 */
class InlineTextEdit extends TextEdit {
	/** @var string The HTML class name used to identify inline editors. */
	private const HtmlClassName = "eq-inline-text-edit";

	/** @var string The name of the API function that will be called on update. */
	private $m_apiFn = null;

	/** @var string The name of the URL parameter that will provide the user's input to the API function. */
	private $m_apiParamName = "value";

	/** @var array The other arguments to provide to the API function. */
	private $m_otherArgs = [];

//	/**
//	 * @var string|null The runtime js callable that will process the results of the API call and provide content
//	 * for the suggestions list.
//	 */
//	private $m_resultProcessor = null;

	/**
	 * Create a new inline text edit widget.
	 *
	 * By default, a widget with no ID is created.
	 *
	 * @param $type int _optional_ The type of widget to create.
	 * @param $id string _optional_ The ID of the text edit widget.
	 */
	public function __construct(int $type = TextEdit::SingleLine, ?string $id = null) {
		parent::__construct($type, $id);
	}

	/**
	 * Set the type of the inline text edit widget.
	 *
	 * The type must be one of SingleLine, Email, Url or Search. Anything else is considered an error. Specifically,
	 * inline edits cannot be _Password_ or _MultiLine_ types.
	 *
	 * @param $type int The widget type.
	 *
	 * @return bool _true_ if the type was set, _false_ otherwise.
	 */
	public function setType(int $type): bool {
		if($type == self::SingleLine || $type == self::Email || $type == self::Url || $type == self::Search) {
			return parent::setType($type);
		}

		AppLog::error("invalid type", __FILE__, __LINE__, __FUNCTION__);
		return false;
	}

	/**
	 * Set the API call that the inline editor uses to submit the user's entry.
	 *
	 * If **$parameterName** is not given, or is _null_, the current parameter name will be retained. It is initially
	 * set to "value".
	 *
	 * The API function will be called repeatedly as the user types to fetch suggestions for what the user might be
	 * typing. The API call will be provided with one URL parameter, named with the given parameter name, which will
	 * receive the current value of the text edit.
	 *
	 * The API function will be called using the _Application.doApiCall()_ function. The function is expected to provide
	 * a list of options in the response body, one per line.
	 *
	 * @param $fn string The name of the API function.
	 * @param $contentParameterName string _optional_ The name of the URL parameter to use to provide the user's
	 * current input to the API function.
	 * @param $otherArgs array _optional_ An associative array (_string_ => _string_) of other parameters for the
	 * API function call. Keys must start with an alpha char and be composed entirely of alphanumeric chars and
	 * underscores.
	 *
	 * @return bool _true_ if the API function was set successfully, _false_ otherwise.
	 */
	public function setSubmitApiCall(string $fn, ?string $contentParameterName = null, array $otherArgs = []): bool {
		$isValidName = function(string $name) {
			return false !== preg_match("/^[a-z][a-z0-9-_]+$/i", $name);
		};

		$doName = isset($contentParameterName);

		if($doName && !$isValidName($contentParameterName)) {
			AppLog::error("invalid API function parameter name", __FILE__, __LINE__, __FUNCTION__);
			return false;
		}

		foreach($otherArgs as $key => $value) {
			if(!is_string($key)) {
				AppLog::error("invalid additional API function call parameter name", __FILE__, __LINE__, __FUNCTION__);
				return false;
			}

			if(!$isValidName($key)) {
				AppLog::error("invalid additional API function call parameter name \"$key\"", __FILE__, __LINE__, __FUNCTION__);
				return false;
			}

			if((!isset($value) && !is_string($value))) {
				AppLog::error("invalid additional API function call argument for parameter \"$key\"", __FILE__, __LINE__, __FUNCTION__);
				return false;
			}
		}

		$this->m_apiFn = $fn;

		if($doName) {
			$this->m_apiParamName = $contentParameterName;
		}

		$this->m_otherArgs = $otherArgs;

		return true;
	}

//	/**
//	 * @param string|null $fn The runtime callable that will process the result of the API call for the edit.
//	 *
//	 * The callable must understand the output of the API function call that returns the suggestions and must
//	 * produce a js array of objects with the following properties:
//	 * - value: `string` the value that the suggestion represents. This is the value that will be placed in the editor if the
//	 *   user selects the suggestion.
//	 * - display: `string|DOM object` the content to display. This is the content that will appear in the
//	 *   suggestions list for the suggestion.
//	 *
//	 * It is recommended that the value and display don't diverge too much. The intention in separating them is to
//	 * provide the ability to annotate suggestions where appropriate.
//	 *
//	 * @return bool `true` if the processor was set, `false`  if not.
//	 */
//	public function setAutocompleteApiResultProcessor(?string $fn): bool {
//		// TODO how can this be validated?
//		$this->m_resultProcessor = $fn;
//		return true;
//	}

	/**
	 * Fetch the URLs of the runtime support javascript modules.
	 *
	 * @return array[string] The support javascript URLs.
	 */
	public static function runtimeScriptUrls(): array {
		return ["js/InlineTextEdit.js"];
	}

	/**
	 * Generate the HTML for the widget.
	 *
	 * This method generates UTF-8 encoded XHTML5.
	 *
	 * @return string The HTML.
	 */
	public function html(): string {
		$classNames = $this->classNames();
		$id         = $this->id();

		if(empty($id)) {
			$id = self::generateUid();
		}

		$hasClass = is_array($classNames) && in_array(self::HtmlClassName, $classNames);

		if(!$hasClass) {
			$this->addClassName(self::HtmlClassName);
		}

		$apiFn = html($this->m_apiFn ?? "");
		$apiParamName = html($this->m_apiParamName ?? "");
		$classNames = $this->classNamesString();
		$placeholder = $this->placeholder();
		$name = $this->name();
		$value = $this->text();
		$htmlValue = html($value ?? "");
		$tt = $this->tooltip();

		$ret = "<div id=\"" . html($id) . "\" class=\"" . html($classNames) . "\" data-api-function-name=\"$apiFn\" data-api-function-content-parameter-name=\"$apiParamName\"";

		foreach($this->m_otherArgs as $paramName => $paramValue) {
			$ret .= " data-api-function-parameter-" . html($paramName) . "=\"" . html($paramValue) . "\"";
		}

		$styleAttr = $this->attribute("style");

		if(isset($styleAttr)) {
			$ret .= $this->emitAttribute("style", $styleAttr);
		}

//		if(isset($this->m_resultProcessor)) {
//			$ret .= " data-api-function-response-processor=\"" . html($this->m_resultProcessor) . "\"";
//		}

		$ret .= "><span class=\"" . self::HtmlClassName . "-display\">$htmlValue</span><input style=\"display: none;\" class=\"" . self::HtmlClassName . "-editor\" type=\"";

		switch($this->type()) {
			// type = MultiLine and type=Password are not supported with inline edits
			default:
			case TextEdit::SingleLine:
				$ret .= "text";
				break;

			case TextEdit::Email:
				$ret .= "email";
				break;

			case TextEdit::Url:
				$ret .= "url";
				break;

			case TextEdit::Search:
				$ret .= "search";
				break;
		}

		if(!empty($placeholder)) {
			$ret .= "placeholder=\"" . html($placeholder) . "\" ";
		}

		if(!empty($name)) {
			$ret .= "name=\"" . html($name) . "\" ";
		}

		if(!empty($value)) {
			$ret .= "value=\"$htmlValue\" ";
		}

		if(!empty($tt)) {
			$ret .= "title=\"" . html($tt) . "\" ";
		}

		$ret .= "/></div>";

		if(!$hasClass) {
			$this->removeClassName(self::HtmlClassName);
		}

		return $ret;
	}
}