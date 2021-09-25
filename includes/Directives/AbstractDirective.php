<?php

namespace WP_Forge\Command\Directives;

use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\DependencyInjection;

/**
 * Class AbstractDirective
 */
abstract class AbstractDirective {

	use CLIOutput, DependencyInjection;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	abstract public function initialize( array $args );

	/**
	 * Validate the directive properties.
	 */
	abstract public function validate();

	/**
	 * Execute the directive.
	 */
	abstract public function execute();

}
