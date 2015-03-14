<?php
/**
 * Plugin Name: TZ Guard
 * Plugin URI: http://www.templaza.com/tz-guard-site-security-wordpress-plugin/542.html
 * Description: This is a simple plugin which will help you to security your WordPress site.
 * Version: 0.1.1
 * Author: TemPlaza
 * Author URI: http://www.templaza.com
 * License: GPLv2 or later
 */

if (!class_exists('TZGuard')) :
    class TZGuard {
        /**
         * Function Contruct
         */
        function __construct() {
            add_action( 'admin_init', array( $this, 'tzguard_i18n' ) );
            add_action( 'admin_menu', array( $this, 'themecheck_add_page' ) );
            add_action( 'init', array( $this, 'check_securitycode') );
            add_action( 'init', array( $this, 'check_blacklist_bot'));

        }

        /**
         * Init Language and textdomain
         */
        function tzguard_i18n() {
            load_plugin_textdomain( 'tz-guard', false, 'tz-guard/languages' );
        }

        /**
         * Add menu page and register settings
         */
        function themecheck_add_page() {
            $hook = add_management_page( 'TZ Guard', 'TZ Guard', 'manage_options', 'tz-guard', array( $this, 'getconfig' ) );
            add_action( 'admin_init', array( $this, 'register_mysettings' ) );
            add_action( "load-$hook", array( $this, 'admin_help' ) );
        }

        /**
         * Register setting options
         */
        function register_mysettings() {
            register_setting( 'tzguard-settings-group', 'tz_securitycode' );
            register_setting( 'tzguard-settings-group', 'tz_black_ip' );
            register_setting( 'tzguard-settings-group', 'tz_bot_enable' );
        }

        /**
         * Check Blacklist and Robot
         */
        function check_blacklist_bot() {
            $yourip	= $this->getRealIpAddr();
            $lists = $this->getServer ();

            if (get_option('tz_bot_enable',1)==-1) {
                if (($lists ['type'] == 'bot') || ($lists ['type'] == 'dow') || ($lists ['type'] == 'lib')) {
                    die(_e('Anti-Bot from '.get_bloginfo('name')));
                }
            }

            if ($black_ip = trim(get_option('tz_black_ip'))) {
                $arr_ip = preg_split('/\n/', $black_ip);
                for ($i = 0; $i < count($arr_ip); $i++) {
                    if ($this->black_ip($yourip, trim($arr_ip[$i]))) {
                        die(_e('('.$yourip.') Your IP has been banned. Please contact customer support if this is in error. ').get_bloginfo('admin_email'));
                    }
                }
            }
        }

        /**
         * Check security code
         */
        function check_securitycode() {
            if ($this->is_login_page() && $securitycode = trim(get_option('tz_securitycode'))) {
                $securitydata	=	@$_GET[$securitycode];

                if (!is_user_logged_in() && !isset($securitydata)) {
                    $beredirect = @$_GET['redirect_to'];
                    if (isset($beredirect)) {
                        $url = parse_url($beredirect);
                        if ($securitycode!= $url['query']){
                            wp_redirect(site_url());
                        }
                    } else {
                        wp_redirect(site_url());
                    }
                }
            }
        }

        /**
         * Check if in login page.
         * @return bool
         */
        function is_login_page() {
            return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
        }

        /**
         * Display admin configuration
         */
        function getconfig() {
            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'tz-guard' ) );
            }
            echo '<div id="tz-guard" class="wrap">';
            echo '<div id="icon-themes" class="icon32"><br /></div><h2>TZ Guard</h2>';
            echo '<div class="tzguard">';
            if( isset($_GET['settings-updated']) ) { ?>
                <div id="message" class="updated">
                    <p><strong><?php echo esc_html__('Settings saved.','tz-guard') ?></strong></p>
                </div>
            <?php } ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'tzguard-settings-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Security Code','tz-guard'); ?></th>
                        <td><input type="text" name="tz_securitycode" value="<?php echo get_option('tz_securitycode'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Black IP','tz-guard'); ?></th>
                        <td><textarea name="tz_black_ip" rows="5"><?php echo get_option('tz_black_ip'); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Allow Robot Access','tz-guard'); ?></th>
                        <td>
                            <input type="radio" name="tz_bot_enable" value="<?php echo esc_attr( '-1' ); ?>" <?php checked( !get_option('tz_bot_enable')?1:get_option('tz_bot_enable'), -1 ); ?> /> <?php echo esc_html__('No','tz-guard'); ?>
                            &nbsp;&nbsp;&nbsp;
                            <input type="radio" name="tz_bot_enable" value="<?php echo esc_attr( '1' ); ?>" <?php checked( !get_option('tz_bot_enable')?1:get_option('tz_bot_enable'), 1 ); ?> /> <?php echo esc_html__('Yes','tz-guard'); ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php
            echo '</div> <!-- .tz-guard-->';
            echo '</div>';
        }

        /**
         * Black IP function
         * @param $yourip string
         * @param $black_ip string
         */
        function black_ip($yourip, $black_ip){
            $arr_blackip	=	preg_split('/\./', $black_ip);
            $arr_yourip		=	preg_split('/\./', $yourip);
            for ($i = 0; $i < count($arr_yourip) && $i < count($arr_blackip); $i++) {
                if ($arr_yourip[$i] != $arr_blackip[$i] && $arr_blackip[$i] != "*") {
                    return FALSE;
                }
            }
            return TRUE;
        }

        /**
         * Return domain in a link.
         * @param $link
         * @return mixed
         */
        function onlydomain($link) {
            $referer = parse_url ( $link );
            $domain = $referer ['host'];
            if (preg_match ( "/www/i", $domain )) {
                $only_domain = str_replace ( 'www.', '', $domain );
                $domain = $only_domain;
            }
            return $domain;
        }

        /**
         * Return type of visitor
         * @param $referer
         * @return string
         */
        function getTraffic($referer) {
            if (($referer == '') || ($referer == $_SERVER ['HTTP_HOST'])) {
                return 'direct';
            }

            $organic = '/google|yahoo\.com|search\.com|live\.com|msn\.com|baidu\.com|altavista\.com|aol\.com|ask\.com|yandex\.com/i';
            if (preg_match ( $organic, $referer )) {
                return 'organic';
            }

            return 'referral';
        }

        /**
         * Get victim information
         * @return mixed
         */
        function getServer() {
            require_once(__DIR__.'/helpers/browser_detection.php');
            $list ['referer'] = isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '';
            $list ['referer'] = $list ['referer'] == '' ? '' : $this->onlydomain ( $list ['referer'] );
            $browser = new TZ_Guard_Browser_Detect ();
            $list ['type'] = $browser->browser_detection ( 'type' );
            $list ['browser_number'] = $browser->browser_detection ( 'number' );
            $list ['browser'] = $browser->browser_detection ( 'browser' );
            $list ['os'] = $browser->browser_detection ( 'os' );
            $list ['os_number'] = $browser->browser_detection ( 'os_number' );
            $list ['uri'] = $_SERVER ['REQUEST_URI'];
            return $list;
        }

        /**
         * Convert IP.
         * @param $ipaddress
         * @return number
         */
        function convertip($ipaddress) {
            $arr_ip = split ( '\.', $ipaddress );
            $bin = '';
            foreach ( $arr_ip as $sip ) {
                $sbin = decbin ( intval ( $sip ) );
                $bin .= str_repeat ( '0', 8 - strlen ( $sbin ) ) . $sbin;
            }
            return bindec ( $bin );
        }

        // get IP
        function getRealIpAddr() {
            if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) //check ip from share internet
            {
                $ip = $_SERVER ['HTTP_CLIENT_IP'];
            } elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) //to check ip is pass from proxy
            {
                $ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER ['REMOTE_ADDR'];
            }
            return $ip;
        }

        /**
         * Add help to the TZ Guard page
         * @return false if not the TZ Guard page
         */
        public static function admin_help() {
            $current_screen = get_current_screen();

            // Screen Content
            if ( current_user_can( 'manage_options' ) ) {
                //configuration page
                $current_screen->add_help_tab(
                    array(
                        'id'		=> 'overview',
                        'title'		=> __( 'Overview' , 'tz-guard'),
                        'content'	=>
                            '<p><strong>' . esc_html__( 'TZ Guard Configuration' , 'tz-guard') . '</strong></p>' .
                            '<p>' . esc_html__( 'This is a simple plugin which will help you to security your WordPress site. The administrator will be protected by a security code. Furthermore you can define a blacklist IP to refuse connection from spam ip and block the BOT system to access your WordPress site.' , 'tz-guard') . '</p>' ,
                    )
                );

                $current_screen->add_help_tab(
                    array(
                        'id'		=> 'security_code',
                        'title'		=> __( 'Security Code' , 'tz-guard'),
                        'content'	=>
                            '<p><strong>' . esc_html__( 'Security Code Configuration' , 'tz-guard') . '</strong></p>' .
                            '<p>' . esc_html__( 'The administrator will be protected by a security code. Note: The first key has to a alphabet character. Ex: temp123' , 'tz-guard') . '</p>' ,
                    )
                );

                $current_screen->add_help_tab(
                    array(
                        'id'		=> 'black_ip',
                        'title'		=> __( 'Black IP' , 'tz-guard'),
                        'content'	=>
                            '<p><strong>' . esc_html__( 'Black IP Configuration' , 'tz-guard') . '</strong></p>' .
                            '<p>' . esc_html__( 'You can define a blacklist IP to refuse connection from spam ip. Input your blacklist ip which one per row. For example: 127.0.0.1 or 127.0.*' , 'tz-guard') . '</p>' ,
                    )
                );

                $current_screen->add_help_tab(
                    array(
                        'id'		=> 'bot_access',
                        'title'		=> __( 'Allow Robot Access' , 'tz-guard'),
                        'content'	=>
                            '<p><strong>' . esc_html__( 'Bot Access Configuration' , 'tz-guard') . '</strong></p>' .
                            '<p>' . esc_html__( 'You can block the BOT system to access your site. Choose "No" if you don\'t want to Bot access yoursite' , 'tz-guard') . '</p>' ,
                    )
                );
            }
        }
    }
    new TZGuard;
endif;