<?php

namespace WP_Forge\Command\Prompts;

/**
 * Class Multiline
 */
class Multiline extends AbstractPrompt {

	/**
	 * Render the prompt.
	 *
	 * return $this
	 */
	public function render() {

		// Get the value from the user
		$this->value = $this->cli()->input( $this->message() )->multiLine()->prompt();

		return $this;
	}

}
