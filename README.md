# T5 Taxonomy Location

This plugin creates a new taxonomy for posts, pages and attachments named _location_.

It was build to show how the [new parameter][1] `'show_admin_column'` can be 
used in WordPress 3.5. If you don’t want to wait until then take Scribu’s class 
[`APP_Tax_Admin_Column`][2].

Further explanations can be found in the mentioned Trac ticket and (in German) in 
my blog article [WordPress: Automatische Taxonomiespalte][3].

To use this plugin just [download the latest master file][4], rename the 
directory to `t5-taxonomy-location` and move it to your blog. On activation new
rewrite rules will be created, and you should see a new entry for the location
in your admin menu.

English and German are done; I would be very happy to get *more translations*. :)

[1]: http://core.trac.wordpress.org/ticket/21240
[2]: https://gist.github.com/3098902
[3]: http://toscho.de/2012/wordpress-automatische-taxonomiespalte/
[4]: https://github.com/toscho/t5-taxonomy-location/zipball/master