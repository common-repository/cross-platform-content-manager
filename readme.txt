=== Cross Platform Content Manager ===
Contributors: ionedigital
Tags: content content synchronization, remote sites, cross-site publishing, API integgration, media synchronization
Requires PHP: 7.0
Requires at least: 5.4
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A fast and reliable solution for synchronizing all types of posts and their taxonomies across remote sites.

== Description ==
Cross-Platform Content Manager: Streamlining WordPress Content Management Across Multiple Sites

Elevate your WordPress experience with Cross-Platform Content Manager, an innovative plugin designed for seamless content synchronization across various WordPress sites. CPCM is a game-changer for webmasters and content managers handling multiple websites. It eliminates the need for repetitive logins and content updates on each site.

Key Features:

- Effortless Synchronization: Automatically syncs various post types, including associated categories and tags, across multiple sites.
- Taxonomy Creation: Generates necessary taxonomies on remote sites, ensuring a consistent structure.
- Media Synchronization: Seamlessly integrates images, storing them in the media library of remote sites and linking them correctly within posts.
- Selective Content Sync: Offers the flexibility to choose specific post types for synchronization.
- User-Friendly Setup: A straightforward configuration process – simply add the site URL, username, and application password for secure synchronization.
Cross-Platform Content Manager is perfect for WordPress users seeking a robust solution to manage content distribution across multiple platforms efficiently. Enhance your content strategy and save time with Cross-Platform Content Manager – the ultimate tool for cross-site content management and publication automation in WordPress.

== License ==
This plugin is licensed under the GNU General Public License, version 2 (GPLv2) or later.
- The full text of the license is available at [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html).
- GPL is a wide open license that grants broad permissions, allowing for freedom of use, modification, and distribution.
- Under this license, anyone is free to use, modify, and redistribute this software.

== Installation ==
1. Upload the plugin files to the '/wp-content/plugins/cpcm' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the settings screen to configure the plugin (CPCM menu in your admin panel).

== 3rd Party or External Service Integration ==

This plugin interacts with third-party services, which users manually add through the plugin's settings page. Third-party services are the addresses of remote sites for further synchronization of posts, their taxonomies, and images between them and the site where our plugin is installed.

How it works:

1. Go to the plugin's settings page in the WordPress admin panel.
2. In the Remote Sites tab, using the form, add remote sites for data synchronization:
- Site name for distinction among other added sites
- Site address, including the https:// protocol. For example: https://mysite.com
- Username of the remote site, who is allowed to publish and edit content
- Application code of the user for protecting your data.
The application code is a unique code for the user of the site, which we use as one of the modern standards for security and protection of data transmission. It can be generated in the user settings in the WordPress admin panel.

Attention! By adding any resource, the user personally assumes responsibility to ensure that the use of this resource for synchronization complies with all relevant laws, privacy policies, and terms of use. We recommend that you first familiarize yourself with the site's privacy policy or terms of use before adding it to the list for synchronization. Typically, this information is displayed on the page titled Privacy Policy or Terms and Conditions. If you have doubts about using a third-party service, we advise seeking legal assistance.

3. On the Sync Settings tab, enable the types of posts for synchronization (Posts, Pages, or both).
4. Create a new page or post and select the available remote sites for synchronization in the Sites to Sync section.
5. Publish the post, and it will automatically be published on the selected sites.
6. Data transmitted during synchronization: post image, its content with added images, taxonomies (categories, tags, etc.) to which the published post belongs.
7. The plugin performs full synchronization of posts. That is, if you change its status or delete the post after publication, the same will happen on the remote site with which this post is synchronized.
8. The plugin and its developers do not store any information about users or the information transmitted between their remote sites in any way.
9. The plugin uses modern security standards to protect transmitted data. Among them are the use of Application Code, data sanitization, disinfection, and verification before sending, among others.

Attention! The user personally bears any responsibility for the data transmitted between sites using our plugin. Ensure that your interaction with our plugin complies with local legal norms and GDPR standards. Ensure compatibility with other international norms and regulations regarding data protection if you use the plugin on a global scale.
If you have any questions about using the plugin, please contact our technical support.

== Frequently Asked Questions ==
*This section will be updated as users inquire about common issues or concerns.*

= 1.0.0 =
* Initial release of Cross-Platform Content Manager.

== Upgrade Notice ==
*Not applicable for the initial release.*

== Additional Notes ==
*There are no additional notes at this time.*
