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
define('DB_NAME', 'omextre358');

/** MySQL database username */
define('DB_USER', 'omextre358');

/** MySQL database password */
define('DB_PASSWORD', 'gnUrpc2ByJxg');

/** MySQL hostname */
define('DB_HOST', 'omextre358.mysql.db:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '4W7OGiitca6q77Fr9sPA7zX7tG2ftDcMG3lNM+kg2CeUaSXcqeB+73T8JJM6');
define('SECURE_AUTH_KEY',  'mdTIcY4ZGd5A1TdamLJBfHhuhFvIsVdaY9FpzNuyyeyc0zfHzAayRsWdggNo');
define('LOGGED_IN_KEY',    'AClGZlhi0WfeyLvWZ+IEBY68VmpcHFrN+5sT+jKaqmZIUCGhvk6lLr/1bem/');
define('NONCE_KEY',        'a1p9/sjXEsTEJbYLbxXko+FfLS+GtMH99LvJzrYumFgnEKbMOlFK6Mh8Lj9u');
define('AUTH_SALT',        'tHqECb/pbW96Ggl/yTVeKOq8I0Sg6oSSiJFyNJXir5FEk/p9HiBRMTaMJ4IR');
define('SECURE_AUTH_SALT', 'rMSx/gaeZp/97vHPDmMIVXoQ5N54G4EPxBgCoYBAUDvGU/9pM9qhOWX7qXi2');
define('LOGGED_IN_SALT',   '2uiYd7Jcn2DJZIVBClJC8HDWjWDcqH8zkD88/1APmaLFuLLuQuCJYTJsrogQ');
define('NONCE_SALT',       'b3bzSrsZbSlYMtqZ5YH9G6tNbhngwoNgNPt4eziGgPscejfSep7hasPix4uX');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'mod826_';

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

/* Fixes "Add media button not working", see http://www.carnfieldwebdesign.co.uk/blog/wordpress-fix-add-media-button-not-working/ */
define('CONCATENATE_SCRIPTS', false );

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
