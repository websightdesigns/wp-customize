## WP Customize

by [@websightdesigns](http://www.websightdesigns.com/)

This plugin allows you to set up a custom login page and set a custom footer message in the WordPress Admin.

### Description

This plugin allows you to set up a custom login page, including your logo. It also allows you to set a custom footer by adding in your own text or HTML.

* Allows you to specify a URL to an image you'd like to use as your Wordpress Admin login page's logo.
* Allows you to specify your own text and/or HTML to replace the footer of the Wordpress Admin with.
* Sets the URL of your blog as the URL visited when a user clicks the logo on the Wordpress Admin login page.
* Sets the title (seen when you hover your mouse over the logo) of the logo's link to be your blog's name.
* Allows you to specify the background color and the text/links color of the Wordpress Admin login page.

Please take a moment and rate this plugin at:
https://wordpress.org/support/plugin/wp-customize/reviews/

The WordPress Codex has a great write-up on Styling Your Login:
https://codex.wordpress.org/Customizing_the_Login_Form#Styling_Your_Login

For support please raise an issue at https://github.com/websightdesigns/wp-customize/issues

### Installation

#### Manual Installation

1. Upload the `wp-customize` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Fill out the settings under `SETTINGS > CUSTOMIZE` in the WordPress Admin

#### Git Submodule Installation

To install this plugin as a submodule of your existing WordPress website repository, you can add it as a submodule:

    git submodule add ssh://user@domain.com/path/to/repository.git

To pull the latest commits of the submodule change into the directory and run `git pull`.

Or, to pull the latest commits of all your repository's submodules, run:

    git submodule foreach git pull origin master

#### WordPress Plugins Repository

You can also install this plugin through the WordPress plugins repository by Navigating to `PLUGINS > ADD NEW` or by visiting https://wordpress.org/plugins/wp-customize/