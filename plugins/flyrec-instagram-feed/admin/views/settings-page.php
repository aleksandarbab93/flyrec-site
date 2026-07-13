<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * @var bool   $connected
 * @var bool   $expired
 * @var int|null $days_left
 * @var string $username
 * @var int    $connected_at
 * @var array  $last_sync
 * @var int    $next_sync
 * @var array  $settings
 * @var string $app_id
 */
$content_types = Fig_Helpers::content_types();
?>
<div class="wrap fig-wrap">
    <h1><?php esc_html_e( '📸 Flyrec Instagram Feed', 'flyrec-instagram-feed' ); ?></h1>
    <p class="description">
        <?php esc_html_e( 'Automatski prikaz Instagram objava (Reels, video, foto, carousel) preko zvaničnog Instagram Graph API-ja.', 'flyrec-instagram-feed' ); ?>
    </p>

    <div id="fig-notice" class="notice" style="display:none;"><p></p></div>

    <!-- ================= KONEKCIJA ================= -->
    <div class="fig-card">
        <h2><?php esc_html_e( '1. Konekcija sa Instagram nalogom', 'flyrec-instagram-feed' ); ?></h2>

        <?php if ( $connected && ! $expired ) : ?>
            <div class="fig-status fig-status--ok">
                ✅ <?php printf(
                    /* translators: %s: Instagram username */
                    esc_html__( 'Povezano sa @%s', 'flyrec-instagram-feed' ),
                    esc_html( $username )
                ); ?>
                <?php if ( null !== $days_left ) : ?>
                    <span class="fig-muted">
                        — <?php printf(
                            /* translators: %d: number of days */
                            esc_html__( 'token ističe za %d dana', 'flyrec-instagram-feed' ),
                            (int) $days_left
                        ); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php elseif ( $connected && $expired ) : ?>
            <div class="fig-status fig-status--error">
                ⚠️ <?php esc_html_e( 'Token je istekao. Generiši nov token i nalepi ga ispod da bi sinhronizacija ponovo radila.', 'flyrec-instagram-feed' ); ?>
            </div>
        <?php else : ?>
            <div class="fig-status fig-status--warn">
                ⏳ <?php esc_html_e( 'Nalog još nije povezan.', 'flyrec-instagram-feed' ); ?>
            </div>
        <?php endif; ?>

        <p class="fig-muted">
            <?php esc_html_e( 'Token se generiše kroz Metin zvanični Graph API Explorer (developers.facebook.com) — pogledaj README.md u folderu plugina za korak-po-korak uputstvo. Token se čuva enkriptovan i nikad se ne prikazuje na sajtu.', 'flyrec-instagram-feed' ); ?>
        </p>

        <table class="form-table">
            <tr>
                <th><label for="fig_token_input"><?php esc_html_e( 'Access Token', 'flyrec-instagram-feed' ); ?></label></th>
                <td>
                    <input type="password" id="fig_token_input" class="regular-text" autocomplete="off" placeholder="<?php esc_attr_e( 'Nalepi Instagram access token ovde', 'flyrec-instagram-feed' ); ?>">
                    <?php if ( $connected ) : ?>
                        <p class="description"><?php echo esc_html( Fig_Token_Manager::masked_token() ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="fig_app_secret_input"><?php esc_html_e( 'Instagram App Secret', 'flyrec-instagram-feed' ); ?> <span class="fig-muted">(<?php esc_html_e( 'opciono', 'flyrec-instagram-feed' ); ?>)</span></label></th>
                <td>
                    <input type="password" id="fig_app_secret_input" class="regular-text" autocomplete="off">
                    <p class="description"><?php esc_html_e( 'Ako je unet, token nalepljen gore se automatski produžava na long-lived (60 dana). Bez ovoga se pretpostavlja da je nalepljeni token već long-lived.', 'flyrec-instagram-feed' ); ?></p>
                </td>
            </tr>
        </table>

        <p>
            <button type="button" class="button button-primary" id="fig-btn-connect"><?php esc_html_e( 'Sačuvaj i poveži', 'flyrec-instagram-feed' ); ?></button>
            <?php if ( $connected ) : ?>
                <button type="button" class="button" id="fig-btn-refresh"><?php esc_html_e( 'Osveži token sada', 'flyrec-instagram-feed' ); ?></button>
                <button type="button" class="button button-link-delete" id="fig-btn-disconnect"><?php esc_html_e( 'Prekini vezu', 'flyrec-instagram-feed' ); ?></button>
            <?php endif; ?>
        </p>
    </div>

    <!-- ================= SINHRONIZACIJA ================= -->
    <div class="fig-card">
        <h2><?php esc_html_e( '2. Sinhronizacija', 'flyrec-instagram-feed' ); ?></h2>

        <p>
            <strong><?php esc_html_e( 'Poslednja sinhronizacija:', 'flyrec-instagram-feed' ); ?></strong>
            <?php echo $last_sync['at'] ? esc_html( Fig_Helpers::format_date( $last_sync['at'] ) . ' — ' ) : esc_html__( 'nikad', 'flyrec-instagram-feed' ); ?>
            <?php if ( $last_sync['at'] ) : ?>
                <span class="<?php echo 'success' === $last_sync['status'] ? 'fig-ok-text' : 'fig-error-text'; ?>">
                    <?php echo esc_html( $last_sync['message'] ); ?>
                </span>
            <?php endif; ?>
        </p>
        <p>
            <strong><?php esc_html_e( 'Sledeća automatska sinhronizacija:', 'flyrec-instagram-feed' ); ?></strong>
            <?php echo $next_sync ? esc_html( Fig_Helpers::format_date( $next_sync ) ) : esc_html__( 'nije zakazana (ručni mod)', 'flyrec-instagram-feed' ); ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'fig_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="fig_sync_interval"><?php esc_html_e( 'Interval sinhronizacije', 'flyrec-instagram-feed' ); ?></label></th>
                    <td>
                        <select name="fig_settings[sync_interval]" id="fig_sync_interval">
                            <option value="hourly" <?php selected( $settings['sync_interval'], 'hourly' ); ?>><?php esc_html_e( 'Jednom na sat', 'flyrec-instagram-feed' ); ?></option>
                            <option value="twicedaily" <?php selected( $settings['sync_interval'], 'twicedaily' ); ?>><?php esc_html_e( 'Dva puta dnevno', 'flyrec-instagram-feed' ); ?></option>
                            <option value="daily" <?php selected( $settings['sync_interval'], 'daily' ); ?>><?php esc_html_e( 'Jednom dnevno', 'flyrec-instagram-feed' ); ?></option>
                            <option value="manual" <?php selected( $settings['sync_interval'], 'manual' ); ?>><?php esc_html_e( 'Samo ručno', 'flyrec-instagram-feed' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <!-- ================= PRIKAZ ================= -->
            <h2><?php esc_html_e( '3. Prikaz na sajtu', 'flyrec-instagram-feed' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="fig_items_limit"><?php esc_html_e( 'Broj prikazanih objava (podrazumevano)', 'flyrec-instagram-feed' ); ?></label></th>
                    <td><input type="number" min="1" max="50" name="fig_settings[items_limit]" id="fig_items_limit" value="<?php echo esc_attr( $settings['items_limit'] ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th><label for="fig_columns"><?php esc_html_e( 'Broj kolona (desktop)', 'flyrec-instagram-feed' ); ?></label></th>
                    <td><input type="number" min="1" max="6" name="fig_settings[columns]" id="fig_columns" value="<?php echo esc_attr( $settings['columns'] ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Tipovi sadržaja', 'flyrec-instagram-feed' ); ?></th>
                    <td>
                        <?php foreach ( $content_types as $key => $label ) : ?>
                            <label style="display:inline-block;margin-right:16px;">
                                <input type="checkbox" name="fig_settings[content_types][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $settings['content_types'], true ) ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Opis i datum', 'flyrec-instagram-feed' ); ?></th>
                    <td>
                        <label style="display:block;margin-bottom:6px;">
                            <input type="checkbox" name="fig_settings[show_caption]" value="1" <?php checked( $settings['show_caption'] ); ?>>
                            <?php esc_html_e( 'Prikaži kratak opis (caption)', 'flyrec-instagram-feed' ); ?>
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="fig_settings[show_date]" value="1" <?php checked( $settings['show_date'] ); ?>>
                            <?php esc_html_e( 'Prikaži datum objave', 'flyrec-instagram-feed' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><label for="fig_click_action"><?php esc_html_e( 'Ponašanje nakon klika (podrazumevano)', 'flyrec-instagram-feed' ); ?></label></th>
                    <td>
                        <select name="fig_settings[click_action]" id="fig_click_action">
                            <option value="lightbox" <?php selected( $settings['click_action'], 'lightbox' ); ?>><?php esc_html_e( 'Popup/lightbox na sajtu', 'flyrec-instagram-feed' ); ?></option>
                            <option value="embed" <?php selected( $settings['click_action'], 'embed' ); ?>><?php esc_html_e( 'Popup sa Instagram embed-om', 'flyrec-instagram-feed' ); ?></option>
                            <option value="instagram" <?php selected( $settings['click_action'], 'instagram' ); ?>><?php esc_html_e( 'Direktan link ka Instagramu (nova kartica)', 'flyrec-instagram-feed' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Ako embed nije dostupan (nedostaje App ID/Secret ili API greška), sajt automatski pada nazad na link ka Instagramu.', 'flyrec-instagram-feed' ); ?></p>
                    </td>
                </tr>
            </table>

            <!-- ================= NAPREDNO ================= -->
            <h2><?php esc_html_e( '4. Napredno', 'flyrec-instagram-feed' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="fig_app_id"><?php esc_html_e( 'Instagram App ID', 'flyrec-instagram-feed' ); ?> <span class="fig-muted">(<?php esc_html_e( 'opciono, samo za embed prikaz', 'flyrec-instagram-feed' ); ?>)</span></label></th>
                    <td><input type="text" name="fig_app_id" id="fig_app_id" value="<?php echo esc_attr( $app_id ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Pri deinstalaciji plugina', 'flyrec-instagram-feed' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="fig_settings[delete_on_uninstall]" value="1" <?php checked( $settings['delete_on_uninstall'] ); ?>>
                            <?php esc_html_e( 'Obriši sve sinhronizovane objave i podešavanja kada se plugin potpuno deinstalira', 'flyrec-instagram-feed' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'Ako je isključeno (podrazumevano), sinhronizovane objave ostaju u bazi i posle deinstalacije.', 'flyrec-instagram-feed' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Sačuvaj podešavanja', 'flyrec-instagram-feed' ) ); ?>
        </form>

        <hr>

        <p>
            <button type="button" class="button button-primary" id="fig-btn-sync"><?php esc_html_e( 'Sinhronizuj sada', 'flyrec-instagram-feed' ); ?></button>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Fig_CPT::POST_TYPE ) ); ?>" class="button"><?php esc_html_e( 'Pogledaj sinhronizovane objave', 'flyrec-instagram-feed' ); ?></a>
            <button type="button" class="button button-link-delete" id="fig-btn-clear"><?php esc_html_e( 'Obriši sve podatke i sinhronizuj ponovo', 'flyrec-instagram-feed' ); ?></button>
        </p>
    </div>

    <!-- ================= SHORTCODE INFO ================= -->
    <div class="fig-card">
        <h2><?php esc_html_e( '5. Prikaz na stranici', 'flyrec-instagram-feed' ); ?></h2>
        <p><?php esc_html_e( 'Zalepi shortcode na bilo koju stranicu ili post:', 'flyrec-instagram-feed' ); ?></p>
        <code>[flyrec_instagram_feed limit="12" columns="4" type="reels" click_action="lightbox"]</code>
        <p class="description">
            <?php esc_html_e( 'Parametri su opcioni — bez njih se koriste podešavanja sa ove stranice. type: reels, video, image, carousel_album ili all (odvojeno zarezom za više tipova).', 'flyrec-instagram-feed' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'Dostupan je i Gutenberg blok "Flyrec Instagram Feed" (kategorija Widgets) sa istim opcijama kroz vizuelni editor.', 'flyrec-instagram-feed' ); ?>
        </p>
    </div>
</div>
