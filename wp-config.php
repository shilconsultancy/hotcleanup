<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u338187101_eefiuh' );

/** Database username */
define( 'DB_USER', 'u338187101_eefiuh' );

/** Database password */
define( 'DB_PASSWORD', 'T+)et1(fb%' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '|kYUily;S&cD&CoVIrK:)RLRpm|e9GS%KsW@*ON1,a1o%D$(,! 3du&K6q!V@(fN' );
define( 'SECURE_AUTH_KEY',   '%L36<x?e>xFk!se,G#+TZU}!!.q*iyEGKp|9L=]>lk3]VkK&Dd?^[FDUs^P`Y]Rf' );
define( 'LOGGED_IN_KEY',     '9_%^).@;ZC#hg0R|CRFdbk:Cl$zW1EWFu+$Kc4rV2c**(>1$P.zQ!;9O6MF1{*8*' );
define( 'NONCE_KEY',         'oDdz-bS#[mnmX}At=-bk)kI_![prrvQ$b%Z9*r?QiVK3MY:qDmZgHa/eO1zcs}&a' );
define( 'AUTH_SALT',         'xZO:HT4>DnZK;6vx}D(5:De4^v(erWwHr%9vJ}9Yd3X?JkVCuGDOvSVC+49?!]>s' );
define( 'SECURE_AUTH_SALT',  'a(sF%%E!bt}%S?8IWS>9wwaHb-p,.]Ha)}mRRu{uf`XuvAq6GAFfK{Kp7knP}(<:' );
define( 'LOGGED_IN_SALT',    '~AR!-diG87!=*EkaKt.vb+ D.ST3eh4%pZ7j{xtcPV7n[KrRkrgEq9Nen:6|}/>m' );
define( 'NONCE_SALT',        'mgB^NNiNQ[Nxv*By:OoG>oR+Y)^{cU&/IFBw0DFRsaj/*4A1gzP}+h39~!c7^tZI' );
define( 'WP_CACHE_KEY_SALT', 'Kzu(v{V>ni &j6r`_tJWo8,e66%gp@XruTq`PpPDbaWHi%JNSUF}s@*bMRe](Dpk' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'oOSz1pI6z_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
