<?php
/**
 * Load a shortcode to display the main report on the frontend.
 * Shortcode: ghactivity
 * One possible parameter: top_issues, boolean
 *
 * @package Ghactivity
 */

add_shortcode( 'ghactivity', 'jeherve_ghactivity_short_markup' );

/**
 * Get data for a custom report.
 *
 * @param array $chart_data Array of event objects to be used in a chart.
 */
function jeherve_ghactivity_cust_report( $chart_data ) {
	// Check if we are on a page with a shortcode. If not, bail now.
	global $post;

	if (
		empty( $post )
		|| ! has_shortcode( $post->post_content, 'ghactivity' )
	) {
		return $chart_data;
	}

	// Grab original options.
	$options = (array) get_option( 'ghactivity' );

	/**
	 * Let's change our saved options.
	 * End date will be today.
	 * Start date will be 2 weeks ago.
	 */
	$options['date_end'] = esc_attr( date( 'Y-m-d' ) );
	$options['date_start'] = esc_attr( date( 'Y-m-d', strtotime( '-2 weeks' ) ) );

	/**
	 * Filter the list of people included in the reports.
	 *
	 * @since 1.6.0
	 *
	 * @param string|array $people Person or list of people included in the report.
	 */
	$people = apply_filters( 'ghactivity_cust_report_people', '' );

	// Let's get some data for these custom dates.
	$custom_report_data = GHActivity_Reports::get_main_report_data( $options, $people );

	if ( ! empty( $custom_report_data ) ) {
		return $custom_report_data;
	} else {
		return array();
	}
}
add_filter( 'ghactivity_chart_data', 'jeherve_ghactivity_cust_report' );

/**
 * Build shortcode.
 *
 * @param array $atts Array of shortcode attributes.
 */
function jeherve_ghactivity_short_markup( $atts ) {
	$atts = shortcode_atts( array(
		'top_issues' => false,
	), $atts, 'ghactivity' );

	$markup = sprintf(
		'
		<p>From %1$s until %2$s:</p>
		<div id="canvas-holder">
			<canvas id="chart-area-admin"/>
		</div>
		<ul id="ghactivity_admin_report"></ul>
		',
		date_i18n( get_option( 'date_format' ), strtotime( date( 'Y-m-d', strtotime( '-2 weeks' ) ) ) ),
		date_i18n( get_option( 'date_format' ), strtotime( date( 'Y-m-d' ) ) )
	);

	if ( 'true' === $atts['top_issues'] ) {
		// Get a list of Top Issues.
		$markup .= GHActivity_Reports::popular_issues_markup( false );
	}

	/**
	 * Filter the content of the GitHub activity shortcode output.
	 *
	 * @since 1.4.0
	 *
	 * @param string $markup Shortcode HTML markup.
	 */
	return apply_filters( 'ghactivity_shortcode_output', $markup );
}
