<?php

/**
 * Defines the LibEquit\HtmlHorizontalRule class.
 *
 * ### Dependencies
 * - classes/equit/PageElement.php
 *
 * ### Changes
 * - (2019-03) First version of this file.
 *
 * @file HtmlHorizontalRule.php
 * @author Darren Edale
 * @version 1.1.2
 * @package libequit
 * @date Mar 2019
 */

namespace Equit\Html;

/**
 * A `HR` element for use in HTML pages.
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
 * This module does not read any settings.
 *
 * ### Session Data
 * This module does not create a session context.
 *
 * @class LibEquit\HtmlLiteral
 * @author Darren Edale
 * @ingroup libequit
 * @package libequit
 *
 * @actions _None_
 * @aio-api _None_
 * @events _None_
 * @connections _None_
 * @settings _None_
 * @session _None_
 */
class HorizontalRule extends PageElement {
	/**
	 * Generate the HTML for the element.
	 *
	 * @return string The HTML for the HR element.
	 */
	public function html(): string {
		return "<hr{$this->emitAttributes()} />";
	}
}
