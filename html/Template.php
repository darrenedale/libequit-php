<?php
/**
 * Defines the Template page element class.
 *
 * ### Dependencies
 * - Equit\Html\PageElement
 * - Equit\Html\Tooltip
 * - Equit\Html\ChildElements
 *
 * ### Changes
 * - (2019-06) First version of this file.
 *
 * @file Template.php
 * @author Darren Edale
 * @version 1.1.2
 * @package Equit\Html
 * @date Jun 2019
 */

namespace Equit\Html;

/**
 * A page element providing template content.
 *
 * @class Template
 * @author Darren Edale
 * @package \Equit\Html
 *
 * @actions _None_
 * @aio-api _None_
 * @events _None_
 * @connections _None_
 * @settings _None_
 * @session _None_
 */
class Template extends PageElement {
	use Tooltip;
	use ChildElements;

	/**
	 * Create a new Template object.
	 *
	 * The ID parameter is optional. By default, a template with no ID is created.
	 *
	 * @param $id string _optional_ The ID for the template.
	 */
	public function __construct(?string $id = null) {
		parent::__construct($id);
	}

	/**
	 * Generate the opening HTML for the template.
	 *
	 * This is a helper method for use when generating the HTML. It could be useful for subclasses to call so that they
	 * don't need to replicate the common HTML for the start of the section element and need only implement their
	 * custom content.
	 *
	 * The start is generated as a _section_ element with the ID and classes specified by the creator, if any have been
	 * provided.
	 *
	 * @return string The opening HTML.
	 */
	protected function emitSectionStart(): string {
		return "<template{$this->emitAttributes()}>";
	}

	/**
	 * Generate the closing HTML for the template.
	 *
	 * This is a helper method for use when generating the HTML. It could be useful for subclasses to call so that they
	 * don't need to replicate the common HTML for the end of the section element and need only implement their custom
	 * content.
	 *
	 * The end is generated as a closing _section_ tag.
	 *
	 * @return string The closing HTML.
	 */
	protected function emitSectionEnd(): string {
		return "</template>";
	}

	/**
	 * Generate the HTML for the template.
	 *
	 * The section is output as a single _template_ element. The element will have whatever classes and ID are set for
	 * it by the code using the template.
	 *
	 * This method generates UTF-8 encoded HTML 5.
	 *
	 * @return string The HTML.
	 */
	public function html(): string {
		return $this->emitSectionStart() . $this->emitChildElements() . $this->emitSectionEnd();
	}
}
