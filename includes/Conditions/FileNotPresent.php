<?php

namespace WP_Forge\Command\Conditions;

/**
 * Class FileNotPresent
 */
class FileNotPresent extends FilePresent {

	/**
	 * Evaluate whether or not a file exists.
	 *
	 * @return bool
	 */
	public function evaluate() {
		return ! parent::evaluate();
	}
}
