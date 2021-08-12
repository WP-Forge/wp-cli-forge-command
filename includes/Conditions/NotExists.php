<?php

namespace WP_Forge\Command\Conditions;

/**
 * Class NotExists
 */
class NotExists extends Exists {

	/**
	 * Evaluate whether or not the key exists.
	 *
	 * @return bool
	 */
	public function evaluate() {
		return ! parent::evaluate();
	}
}
