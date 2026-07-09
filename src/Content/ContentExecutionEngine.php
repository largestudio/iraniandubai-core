<?php
/**
 * Content execution engine.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Prepares a content decision for downstream execution.
 */
final class ContentExecutionEngine {
	/**
	 * Prepare execution for a content decision.
	 *
	 * @param ContentDecision $decision Content decision.
	 *
	 * @return ContentExecution
	 */
	public function execute( ContentDecision $decision ): ContentExecution {
		$selection = $decision->get_selection();

		if ( null === $selection ) {
			return new ContentExecution(
				$decision,
				'skipped',
				$decision->get_context(),
				null,
				array(
					'reason' => 'no_valid_content',
				)
			);
		}

		return new ContentExecution(
			$decision,
			'ready',
			$decision->get_context(),
			$selection->get_content(),
			array(
				'reason' => 'content_ready',
			)
		);
	}
}
