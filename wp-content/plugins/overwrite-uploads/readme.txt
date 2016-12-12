=== Overwrite Uploads ===
Contributors: iandunn
Donate link: http://kiva.org
Tags: overwrite, uploads, files, media library, unique, filename
Requires at least: 2.9
Tested up to: 4.2
Stable tag: 1.1
License: GPLv2

Overwrites files that already exist when uploading, instead of storing multiple copies with unique filenames.


== Description ==
By default WordPress doesn't overwrite an existing file if you upload a new one with the same name and directory. Instead, it appends a number to the end of the filename in order to make it unique, *e.g., filename1.jpg*.

That isn't always the desired behavior, so this plugin makes it so that any files uploaded will automatically overwrite existing files, rather than creating a second file with a unique name. 

**UPDATE**: This plugin **no longer requires modifying WordPress Core**. You can install it and it'll work automatically, without any other changes.


== Installation ==
For help installing this (or any other) WordPress plugin, please read the [Managing Plugins](http://codex.wordpress.org/Managing_Plugins) article on the Codex.

Once the plugin is installed and activated, it will start to work automatically.



== Frequently Asked Questions ==

= Why do I see multiple copies of the file in the Media Library =
WordPress allows files to have the same name if they're not in the same directory. So, `wp-content/uploads/2013/11/pizza.jpg` can live alongside `wp-content/uploads/2013/12/pizza.jpg`. This plugin only deletes existing files if they are in the same folder that the new file will be placed in. 

= Can I make a donation to support the plugin? =
I do this as a way to give back to the WordPress community, so I don't want to take any donations, but if you'd like to give something I'd encourage you to make a microloan with [Kiva](http://www.kiva.org).

= Do you support this plugin? =
I'm happy to fix reproducable bugs, but I don't have time to help you customize the plugin to fit your specific needs.
 

== Changelog ==

= 1.1 (2013-12-14) =
* [NEW] Delete existing attachments before a new one is uploaded. This avoids the need to modify WordPress core files.
* [UPDATE] General code cleanup and modernization
* [UPDATE] Removed unnecessary debugging method, activation logic and admin notices support
* [UPDATE] Moved requirements check into bootstrap script
* [UPDATE] Moved overwrite-uploads.php into classes directory
* [UPDATE] Fixed improperly capitalized overwriteUploads class name

= 1.0.2 = 
* Fixed bug where old Media Library entries weren't removed if the 'Organize my uploads into year and month folders' setting was enabled.
* Setting removed because it's unnecessary. Plugin will overwrite uploads if activated, and won't if it isn't.

= 1.0.1 = 
* Added network-wide activation for WPMS blogs

= 1.0 =
* Initial release


== Upgrade Notice ==

= 1.1 =
Overwrite Uploads 1.1 now works without having to modify WordPress Core files

= 1.0.2 = 
Overwrite Uploads 1.0.2 fixes a bug where old Media Library entries weren't deleted.

= 1.0.1 =
Overwrite Uploads 1.0.1 adds support for network-side activation on WordPress MultiSite installations

= 1.0 =
Initial release