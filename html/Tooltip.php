<?php
/**
 * Defines the HtmlTooltip trait.
 *
 * ### Dependencies
 * None.
 *
 * ### Changes
 * - (2018-09) First version of this file.
 *
 * @file HtmlTooltip.php
 * @author Darren Edale
 * @version 1.1.2
 * @package libequit
 * @date Sep 2018
 */

namespace Equit\Html;

/**
 * Trait HtmlTooltip.
 *
 * Trait to enable HTML elements to support tooltips without having to reimplement the functionality over and over.
 */
trait Tooltip {
	/**
	 * Set the tooltip for the element.
	 *
	 * The _title_ attribute is used for the tooltip. This is usually presented by graphical user agents as a tooltip.
	 * The tooltip can be _null_ to unset the current tooltip. If it is set to _null_, the _title_ attribute will
	 * usually be omitted from the rendered element altogether.
	 *
	 * @param null|string $tooltip The tooltip to set.
	 */
	public function setTooltip(?string $tooltip): void {
		$this->setAttribute("title", $tooltip);
	}

	/**
	 * Fetch the element's tooltip.
	 *
	 * The tooltip returned will be _null_ if no tooltip is set.
	 *
	 * @return null|string The tooltip.
	 */
	public function tooltip(): ?string {
		return $this->attribute("title");
	}
}
