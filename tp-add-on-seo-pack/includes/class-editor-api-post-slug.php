<?php

class TRP_Editor_Api_Post_Slug {

	/* @var TRP_Query */
	protected $trp_query;
	/* @var TRP_SP_Slug_Manager */
	protected $slug_manager;
	/* @var TRP_Translation_Manager */
	protected $translation_manager;
	/* @var TRP_Url_Converter */
	protected $url_converter;
    protected $settings;

	/**
	 * TRP_Translation_Manager constructor.
	 *
	 * @param array $settings Settings option.
	 */
	public function __construct( $settings, $slug_manager ) {
		$this->settings = $settings;
		$this->slug_manager = $slug_manager;
	}

	/**
	 * Returns translations of slugs
	 *
	 * Hooked to wp_ajax_trp_get_translations_postslug
	 */
	public function postslug_get_translations() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			check_ajax_referer( 'postslug_get_translations', 'security' );
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'trp_get_translations_postslug' && ! empty( $_POST['language'] ) && in_array( $_POST['language'], $this->settings['translation-languages'] ) ) {
				$ids = (empty($_POST['string_ids']) )? array() : json_decode(stripslashes($_POST['string_ids']));
				if ( is_array( $ids )){
					$trp = TRP_Translate_Press::get_trp_instance();
					if (!$this->translation_manager) {
						$this->translation_manager = $trp->get_component('translation_manager');
					}
					$localized_text = $this->translation_manager->string_groups();
					$id_array = array();
					$dictionaries = array();

					foreach ( $ids as $id ) {
						if ( isset( $id ) && is_numeric( $id ) ) {
							$id_array[] = (int) $id;
						}
					}

					foreach( $id_array as $post_id ) {
						$entry = array(
							'dbID'              => $post_id,
							'translationsArray' => array(),
							'type'              => 'postslug',
							'group'             => $localized_text['slugs'],
							'original'          => get_post_field( 'post_name', $post_id )
						);
						foreach ( $this->settings['translation-languages'] as $language ) {
							if ( $language != $this->settings['default-language'] ) {
								$translated = $this->slug_manager->get_translated_slug( $post_id, $language );
								$entry['translationsArray'][$language] = array(
									'id'                => $post_id,
									'translated'        => $translated,
									'editedTranslation' => $translated,
								);
							}
						}
						$dictionaries[] = $entry;
					}
					echo trp_safe_json_encode( $dictionaries );
				}
			}
		}
		wp_die();
	}

	/**
	 * Save translations of slugs
	 *
	 * Hooked to wp_ajax_trp_save_translations_postslug
	 */
	public function postslug_save_translations() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && apply_filters( 'trp_translating_capability', 'manage_options' ) ) {
			check_ajax_referer( 'postslug_save_translations', 'security' );
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'trp_save_translations_postslug' && !empty( $_POST['strings'] ) ) {
				$slugs = json_decode(stripslashes($_POST['strings']));
				$update_slugs = array();
				foreach ( $slugs as $language => $language_slugs ) {
					if ( in_array( $language, $this->settings['translation-languages'] ) && $language != $this->settings['default-language'] ) {
						$update_slugs[ $language ] = array();
						foreach( $language_slugs as $slug ) {
							if ( isset( $slug->id ) && is_numeric( $slug->id ) ) {
								$update_slugs[ $language ][] = array(
									'id' => (int)$slug->id,
									'translated' => $slug->translated,
                                    'status' => (int)$slug->status
								);

							}
						}
					}
				}

                $meta_based_strings   = new TRP_SP_Meta_Based_Strings();
                $translated_slug_meta = array(
                    1 => $meta_based_strings->get_automatic_translated_slug_meta(),
                    2 => $meta_based_strings->get_human_translated_slug_meta()
                );
                foreach( $update_slugs as $language => $update_slugs_array ) {
					foreach( $update_slugs_array as $slug ) {
						if ( ! empty( $slug['id'] ) ) {
                            $post = get_post($slug['id']);
                            if( is_object($post) ) {
                                if ( $translated_slug_meta[$slug['status']] === $meta_based_strings->get_human_translated_slug_meta() || $slug['status'] === 0){
                                    delete_post_meta($slug['id'], $meta_based_strings->get_automatic_translated_slug_meta() . $language );
                                }
                                if ( $translated_slug_meta[$slug['status']] === $meta_based_strings->get_automatic_translated_slug_meta() || $slug['status'] === 0 ){
                                    delete_post_meta($slug['id'], $meta_based_strings->get_human_translated_slug_meta() . $language );
                                }

                                if ( $slug['status'] !== 0 ) {
                                    $sanitized_slug = sanitize_title( $slug['translated'] );
                                    $unique_slug    = $this->slug_manager->get_unique_post_slug( $sanitized_slug, $post, $language );
                                    update_post_meta( $slug['id'], $translated_slug_meta[ $slug['status'] ] . $language, $unique_slug );
                                }
                            }
						}
					}
				}
			}
		}
		echo trp_safe_json_encode( array() );
		wp_die();
	}

}