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
define('DB_NAME', 'jasonlee_db');

/** MySQL database username */
define('DB_USER', 'jasonlee_db');

/** MySQL database password */
define('DB_PASSWORD', 'D!mmak1090');

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
define('AUTH_KEY',         'Le2gKOZ=F@ Y20j3t+)c:7LE!!1<WM:/V gN1W=c Y}S;3O6u)Siw?%-6C(&3P1E');
define('SECURE_AUTH_KEY',  '-kyY`~kbVsU3.vAb5{50Z6~SWJc%MGrVH0}7>]EdS`c%oAT=ueYTV]>akm7J[QE3');
define('LOGGED_IN_KEY',    'yh~g AWl/GE&7{BG-+;.sPqgB^y8X@ [GT}JOe){!#Sx+QhQak8^y1uPl,)j~K{?');
define('NONCE_KEY',        'r@wY=,-&&{o;{E(J_e*4M&DJK4,guW%ay(<^wtBo7QHdXBDeu{zcL58s0`cY20>M');
define('AUTH_SALT',        'eo? m]*9yzBXGhcS.wt@og*6@#DF@`VB{YcV%i`W)|x#0:u|O_q?^:1Fq-+9(=z7');
define('SECURE_AUTH_SALT', '0Q.QknSRBkysv2lO%EvP|/N7q@35% 0$^XCh.W-x/DqA*E8SJ Zq5I@4~ks,~nYO');
define('LOGGED_IN_SALT',   'n&$:REr?$6j@GGz2U;`Uxd]ghPRc=>XFz-RG?+vrw; [4?@G>w7jm|6;_H?:}~$4');
define('NONCE_SALT',       'L:QH_c$FZfiAyABr9J^^m` g<Fk<wxNYfC85EQJh&)CbR(;Xb8Autdn!)l;>-:QS');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
