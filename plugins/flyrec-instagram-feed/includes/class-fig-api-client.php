<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tanak klijent za Instagram Graph API ("Instagram API with Instagram Login").
 *
 * Koristi isključivo WordPress HTTP API (wp_remote_get/post) — nema cURL-a
 * napravo, nema scrapinga. Svaki poziv vraća WP_Error pri neuspehu, nikad
 * ne baca fatalnu grešku ka pozivaocu.
 */
class Fig_Api_Client {

    /** Polja koja tražimo za svaki media objekat. */
    const MEDIA_FIELDS = 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink,timestamp,username,children{media_type,media_url,thumbnail_url}';

    private $token;

    public function __construct( $token = null ) {
        $this->token = $token ?: Fig_Token_Manager::get_token();
    }

    /**
     * Proverava validnost tokena i vraća osnovne podatke o nalogu.
     * Koristi se odmah nakon što admin nalepi novi token.
     *
     * @return array|WP_Error { id, username }
     */
    public function validate_and_fetch_account() {
        $response = $this->request( 'GET', FIG_GRAPH_HOST . '/me', [
            'fields'       => 'id,username,account_type',
            'access_token' => $this->token,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( empty( $response['id'] ) || empty( $response['username'] ) ) {
            return new WP_Error( 'fig_invalid_response', __( 'Instagram API je vratio neočekivan odgovor pri proveri naloga.', 'flyrec-instagram-feed' ) );
        }

        return $response;
    }

    /**
     * Preuzima listu media objekata sa naloga (paginirano).
     *
     * @param int         $limit  Broj stavki po stranici (max 50, API preporuka).
     * @param string|null $after  Kursor za sledeću stranicu.
     * @return array|WP_Error { data: [...], paging: { next_after: string|null } }
     */
    public function get_media( $limit = 25, $after = null ) {
        $ig_user_id = Fig_Token_Manager::get_ig_user_id();
        if ( ! $ig_user_id ) {
            return new WP_Error( 'fig_no_account', __( 'Instagram nalog nije povezan.', 'flyrec-instagram-feed' ) );
        }

        $args = [
            'fields'       => self::MEDIA_FIELDS,
            'access_token' => $this->token,
            'limit'        => min( 50, max( 1, (int) $limit ) ),
        ];
        if ( $after ) {
            $args['after'] = $after;
        }

        $response = $this->request( 'GET', FIG_GRAPH_HOST . '/' . rawurlencode( $ig_user_id ) . '/media', $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return [
            'data'       => isset( $response['data'] ) && is_array( $response['data'] ) ? $response['data'] : [],
            'next_after' => $response['paging']['cursors']['after'] ?? null,
            'has_next'   => ! empty( $response['paging']['next'] ),
        ];
    }

    /**
     * Menja short-lived token za long-lived (60 dana) — koristi se jednom,
     * odmah nakon što admin nalepi token dobijen iz Graph API Explorer-a
     * (koji je po pravilu short-lived).
     *
     * @return array|WP_Error { access_token, expires_in }
     */
    public function exchange_for_long_lived_token( $short_lived_token, $app_secret ) {
        if ( empty( $app_secret ) ) {
            // Bez App Secret-a ne možemo da uradimo exchange — pozivalac treba
            // da pretpostavi da je nalepljeni token već long-lived.
            return new WP_Error( 'fig_no_app_secret', __( 'Nedostaje Instagram App Secret za produženje tokena.', 'flyrec-instagram-feed' ) );
        }

        return $this->request( 'GET', FIG_GRAPH_HOST . '/access_token', [
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => $app_secret,
            'access_token'  => $short_lived_token,
        ] );
    }

    /**
     * Osvežava postojeći long-lived token (produžava mu vek za novih 60 dana).
     * Mora se pozvati pre nego što token istekne.
     *
     * @return array|WP_Error { access_token, expires_in }
     */
    public function refresh_long_lived_token() {
        return $this->request( 'GET', FIG_GRAPH_HOST . '/refresh_access_token', [
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $this->token,
        ] );
    }

    /**
     * Zvanični Instagram oEmbed — koristi se samo kad korisnik klikne na
     * objavu i otvori popup (lazy), nikad na inicijalno učitavanje grida.
     *
     * Napomena: oEmbed endpoint po Metinoj dokumentaciji zahteva App Access
     * Token (client_id|client_secret), ne token korisnika/naloga. Ako App
     * Secret nije unet u podešavanjima, ova funkcija vraća WP_Error i
     * frontend automatski pada nazad na "Otvori na Instagramu" link.
     *
     * @return array|WP_Error { html }
     */
    public function get_oembed( $permalink, $app_id, $app_secret ) {
        if ( empty( $app_id ) || empty( $app_secret ) ) {
            return new WP_Error( 'fig_no_app_credentials', __( 'Embed nije podešen (nedostaje Instagram App ID/Secret).', 'flyrec-instagram-feed' ) );
        }

        return $this->request( 'GET', FIG_FB_GRAPH_HOST . '/' . FIG_API_VERSION . '/instagram_oembed', [
            'url'          => $permalink,
            'access_token' => $app_id . '|' . $app_secret,
            'omitscript'   => false,
        ] );
    }

    /**
     * Zajednički HTTP izvršilac — WP HTTP API, sa timeout-om i doslednim
     * parsiranjem grešaka koje Graph API vraća u JSON telu.
     *
     * @return array|WP_Error
     */
    private function request( $method, $url, array $args = [] ) {
        if ( empty( $this->token ) && false === strpos( $url, 'client_secret' ) ) {
            return new WP_Error( 'fig_no_token', __( 'Instagram access token nije podešen.', 'flyrec-instagram-feed' ) );
        }

        $request_url = add_query_arg( $args, $url );

        $response = wp_remote_request( $request_url, [
            'method'  => $method,
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['error'] ) ) {
            $message = $body['error']['message'] ?? __( 'Nepoznata Instagram API greška.', 'flyrec-instagram-feed' );
            $code_str = $body['error']['code'] ?? $code;
            return new WP_Error( 'fig_api_error_' . $code_str, $message, $body['error'] );
        }

        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'fig_http_error', sprintf(
                /* translators: %d: HTTP status code */
                __( 'Instagram API je vratio HTTP status %d.', 'flyrec-instagram-feed' ),
                $code
            ) );
        }

        return is_array( $body ) ? $body : [];
    }
}
