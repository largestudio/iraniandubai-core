<?php
/**
 * Content selection engine.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Selects the best content match from an ordered candidate list.
 */
final class ContentSelector {

	/**
	 * Select the best content match.
	 *
	 * @param array<int,ContentMatch> $matches Content matches.
	 *
	 * @return ContentSelection|null
	 */
	public function select( array $matches ): ?ContentSelection {
		$best_match = null;

		foreach ( $matches as $match ) {
			if ( ! $match instanceof ContentMatch ) {
				continue;
			}

			if ( null === $best_match || $this->is_better_match( $match, $best_match ) ) {
				$best_match = $match;
			}
		}

		return null !== $best_match ? new ContentSelection( $best_match ) : null;
	}

	/**
	 * Determine whether one match outranks another.
	 *
	 * @param ContentMatch $candidate Candidate match.
	 * @param ContentMatch $current   Current best match.
	 *
	 * @return bool
	 */
	private function is_better_match( ContentMatch $candidate, ContentMatch $current ): bool {
		if ( $candidate->get_score() !== $current->get_score() ) {
			return $candidate->get_score() > $current->get_score();
		}

		return $candidate->get_content()->get_priority() > $current->get_content()->get_priority();
	}
}
