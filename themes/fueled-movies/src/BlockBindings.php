<?php
/**
 * Block Bindings setup
 *
 * @package FueledMoviesTheme
 */

namespace FueledMoviesTheme;

use TenupFramework\Module;
use TenupFramework\ModuleInterface;
use TenUpPlugin\PostTypes\Movie;
use TenUpPlugin\PostTypes\Person;

/**
 * BlockBindings module.
 *
 * @package FueledMoviesTheme
 */
class BlockBindings implements ModuleInterface {

	use Module;

	/**
	 * Can this module be registered?
	 *
	 * @return bool
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Register any hooks and filters.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', [ $this, 'register_block_bindings' ], 11, 0 );
	}

	/**
	 * Register the block bindings for the theme.
	 *
	 * @return void
	 */
	public function register_block_bindings() {
		register_block_bindings_source(
			'tenup/block-bindings',
			array(
				'label'              => __( 'Fueled Movies Theme', 'tenup' ),
				'get_value_callback' => [ $this, 'block_bindings_callback' ],
			)
		);
	}

	/**
	 * Main callback for the 'tenup/block-bindings' source.
	 * Routes to appropriate helper functions based on the key.
	 *
	 * @param array $source_args The args found in the block metadata to create the binding.
	 * @return string|null The binding value or null.
	 */
	public function block_bindings_callback( $source_args ) {
		if ( ! isset( $source_args['key'] ) ) {
			return null;
		}

		switch ( $source_args['key'] ) {
			case 'archiveLinkText':
				return $this->get_archive_link( 'text' );
			case 'archiveLinkUrl':
				return $this->get_archive_link( 'url' );
			case 'movieStars':
				return $this->get_movie_stars();
			case 'personBorn':
				return $this->get_person_born();
			case 'personDied':
				return $this->get_person_died();
			case 'personMovies':
				return $this->get_person_movies();
			case 'viewerRatingLabelText':
				return $this->get_viewer_rating_label( 'text' );
			case 'viewerRatingLabelTextNumberOnly':
				return $this->get_viewer_rating_label( 'number' );
			case 'viewerRatingLabelUrl':
				return $this->get_viewer_rating_label( 'url' );
			default:
				return null;
		}
	}

	/**
	 * Get the archive link text or URL.
	 *
	 * @param string $type The type of value to return ('text' or 'url').
	 * @return string|null The text or URL.
	 */
	private function get_archive_link( $type ) {
		$url         = home_url();
		$referer     = wp_get_referer();
		$archive_url = get_post_type_archive_link( get_post_type() );

		if ( $referer && $archive_url && strpos( $referer, $archive_url ) === 0 ) {
			$url = $referer;
		} elseif ( $archive_url ) {
			$url = $archive_url;
		}

		$value = $url;

		if ( 'text' === $type ) {
			$value = __( '← Back', 'tenup' );
		}

		return $value;
	}

	/**
	 * Get the movie stars as comma-separated linked names.
	 *
	 * @return string|null The linked names HTML or null.
	 */
	private function get_movie_stars() {
		$value   = '';
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		if ( ! function_exists( '\TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
			return $value;
		}

		$star_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

		if ( empty( $star_ids ) ) {
			return $value;
		}

		$stars_query = new \WP_Query(
			[
				'post_type'      => Person::POST_TYPE,
				'post__in'       => $star_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 99,
			]
		);

		if ( ! $stars_query->have_posts() ) {
			return $value;
		}

		$star_links = array_map(
			function ( $star ) {
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_permalink( $star->ID ) ),
					esc_html( $star->post_title )
				);
			},
			$stars_query->posts
		);

		$value = implode( ', ', $star_links );

		return $value;
	}

	/**
	 * Get the person's birth date formatted.
	 *
	 * @return string|null The formatted date or null.
	 */
	private function get_person_born() {
		$value   = '';
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		$born = get_post_meta( $post_id, 'tenup_person_born', true ) ?? '';

		if ( '' === $born ) {
			return $value;
		}

		$value = gmdate( 'F j, Y', strtotime( $born ) );

		return $value;
	}

	/**
	 * Get the person's death date formatted.
	 *
	 * @return string|null The formatted date or null.
	 */
	private function get_person_died() {
		$value   = '';
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		$died = get_post_meta( $post_id, 'tenup_person_died', true ) ?? '';

		if ( '' === $died ) {
			return $value;
		}

		$value = gmdate( 'F j, Y', strtotime( $died ) );

		return $value;
	}

	/**
	 * Get the person's movies as comma-separated linked titles.
	 *
	 * @return string|null The linked titles HTML or null.
	 */
	private function get_person_movies() {
		$value   = '';
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $value;
		}

		if ( ! function_exists( '\TenUp\ContentConnect\Helpers\get_related_ids_by_name' ) ) {
			return $value;
		}

		$movie_ids = \TenUp\ContentConnect\Helpers\get_related_ids_by_name( $post_id, 'movie_person' );

		if ( empty( $movie_ids ) ) {
			return $value;
		}

		$movies_query = new \WP_Query(
			[
				'post_type'      => Movie::POST_TYPE,
				'post__in'       => $movie_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 99,
			]
		);

		if ( ! $movies_query->have_posts() ) {
			return $value;
		}

		$movie_links = array_map(
			function ( $movie ) {
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_permalink( $movie->ID ) ),
					esc_html( $movie->post_title )
				);
			},
			$movies_query->posts
		);

		$value = implode( ', ', $movie_links );

		return $value;
	}

	/**
	 * Get the viewer rating label text or URL.
	 *
	 * @param string $type The type of value to return ('text' or 'url').
	 * @return string The text or URL.
	 */
	private function get_viewer_rating_label( $type ) {
		$value = '#';

		if ( 'text' === $type || 'number' === $type ) {
			$rating = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating', true ) ?? false;

			$star         = '<mark style="background-color: transparent;color:#f5c518" class="has-inline-color">★</mark>';
			$allowed_tags = [
				'mark' => [
					'style' => [],
					'class' => [],
				],
			];

			if ( 'number' === $type ) {
				$text  = false !== $rating ? $rating : '0.0';
				$value = wp_kses( $star . ' ' . $text, $allowed_tags );
			} else {
				$text  = '0/10 (0)';
				$count = get_post_meta( get_the_ID(), 'tenup_movie_viewer_rating_count', true ) ?? false;

				if ( false !== $rating && false !== $count ) {
					$count_display = $count;

					if ( $count >= 1000 && $count < 10000 ) {
						$count_display = number_format( round( $count, -2 ) / 1000, 1, '.', '' ) . 'K';
					} elseif ( $count >= 10000 ) {
						$count_display = number_format( round( $count, -3 ) / 1000, 0, '.', '' ) . 'K';
					}

					$text = $rating . '/10 (' . $count_display . ')';
				}

				$value = wp_kses( $star . $text, $allowed_tags );
			}
		}

		return $value;
	}
}
