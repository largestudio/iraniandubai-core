<?php
/**
 * Dynamic content match value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Represents the result of matching content against runtime contexts.
 */
final class ContentMatch {

	/**
	 * Matched content entity.
	 *
	 * @var Content
	 */
	private Content $content;

	/**
	 * Match score.
	 *
	 * @var int
	 */
	private int $score;

	/**
	 * Contexts matched by the content entity.
	 *
	 * @var array<int,string>
	 */
	private array $matched_contexts;

	/**
	 * Constructor.
	 *
	 * @param Content           $content          Matched content entity.
	 * @param int               $score            Match score.
	 * @param array<int,string> $matched_contexts Contexts matched by the content entity.
	 */
	public function __construct( Content $content, int $score, array $matched_contexts = array() ) {
		$this->content          = $content;
		$this->score            = $score;
		$this->matched_contexts = array_values( $matched_contexts );
	}

	/**
	 * Get the matched content entity.
	 *
	 * @return Content
	 */
	public function get_content(): Content {
		return $this->content;
	}

	/**
	 * Get the match score.
	 *
	 * @return int
	 */
	public function get_score(): int {
		return $this->score;
	}

	/**
	 * Get matched contexts.
	 *
	 * @return array<int,string>
	 */
	public function get_matched_contexts(): array {
		return $this->matched_contexts;
	}
}
