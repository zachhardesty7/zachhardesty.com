<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'fcshar5_wp900');

/** MySQL database username */
define('DB_USER', 'fcshar5_wp900');

/** MySQL database password */
define('DB_PASSWORD', 'p)S2G6G7L.');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'xqnqxfnsceiy4blhyb9mctnphcwlsisa4j4fnabipmaoxcifbe4woyfjeaayqbbf');
define('SECURE_AUTH_KEY',  'mdrh0psceivlj89oet4yhighdgyidgehalztiqxh5gawnslupxdhmjudniza88xq');
define('LOGGED_IN_KEY',    'schzpgrpufano3zyfanz2lstdgpukhxfhnhltdz3o1imphh4czspi6kzla9ny0ck');
define('NONCE_KEY',        'kf3psmtuuoo6b0agx3o9stqq5qidf2a7cx67odhvn3pqncwqncg9hzswprw7nk0q');
define('AUTH_SALT',        'rggmn1kmyjg3pa7anehlgh4ezm51pgzzfnnlffmgkerkisx1zkbaqxnkvf7j1flz');
define('SECURE_AUTH_SALT', 'owxsog4sssrnqxx8jrlasriw3qov1lhurufed953z5dremgpfe79fqv17n4eafkk');
define('LOGGED_IN_SALT',   'brgpbzx3chi2czwgdrmxb6adp4a2n9bpqysjqfbdpdgq842lb8stzlf5bb8q78w2');
define('NONCE_SALT',       'zmjlw5mxv7pnwhdb1zxouvaaq2eubgkizftqm3wa63hrv7fyquza43qfrf8mk9hl');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wprm_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
