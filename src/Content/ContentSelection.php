<?php
/**
 * Content selection value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Represents the selected content match.
 */
final class ContentSelection {

	/**
	 * Selected content match.
	 *
	 * @var ContentMatch
	 */
	private ContentMatch $match;

	/**
	 * Constructor.
	 *
	 * @param ContentMatch $match Selected content match.
	 */
	public function __construct( ContentMatch $match ) {
		$this->match = $match;
	}

	/**
	 * Get the selected content match.
	 *
	 * @return ContentMatch
	 */
	public function get_match(): ContentMatch {
		return $this->match;
	}

	/**
	 * Get the selected content entity.
	 *
	 * @return Content
	 */
	public function get_content(): Content {
		return $this->match->get_content();
	}

	/**
	 * Get the selected match score.
	 *
	 * @return int
	 */
	public function get_score(): int {
		return $this->match->get_score();
	}
}
