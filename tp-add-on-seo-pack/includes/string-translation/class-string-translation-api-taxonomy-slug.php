<?php

class TRP_String_Translation_API_Taxonomy_Slug {
    protected $type        = 'taxonomy-slug';
    protected $option_name = 'trp_taxonomy_slug_translation';
    protected $helper;
    protected $settings;
    protected $option_based_slugs;

    public function __construct( $settings ) {
        $this->settings = $settings;
        $this->helper   = new TRP_String_Translation_Helper();
        $this->option_based_slugs = new TRP_SP_Option_Based_Strings();
    }

    public function get_strings() {
        $this->helper->check_ajax( $this->type, 'get' );

        $all_slugs = $this->option_based_slugs->get_public_slugs( 'taxonomies' );

        $return = $this->option_based_slugs->get_strings_for_option_based_slug($this->type, $this->option_name, $all_slugs );

        echo trp_safe_json_encode( $return );
        wp_die();
    }

    public function save_strings() {

        $this->helper->check_ajax( $this->type, 'save' );

        $this->option_based_slugs->save_strings_for_option_based_slug( $this->type, $this->option_name );
    }
}