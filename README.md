The project is not actively maintained or developed by Scholars' Lab, but anyone should feel free to fork the project and work on it. Pull requests are accepted.

### Dependencies

- php: Requires XSL to be activated. Information can be found at [the PHP:XSL Manual](http://php.net/manual/en/book.xsl.php)
- npm: gulp, gulp-stylus, autoprefixer-stylus
- WP Plugins: Custom Bulk Action plugin by Seravo

## To Enable XSL:
In your php.ini
1. Uncomment `;extension=php_xsl.dll`
2. Change `extension_dir` to `extension_dir = "./ext"`

### Things to know

- In order for the poems to show up, you must set WP Permalinks to Postname (i.e., pretty permalinks) in the WP settings.
- In WP Settings/Writing, make sure that "Wordpress should correct invalidly nested XHTML automatically" is *not* checked. If it is checked, WP will strip out breaks that are necessary for proper display of the poems and rhymebar inputs.

### Poem Upload process

- In Prosody, every poem is an instance of the prosody_poem custom post type. To add a poem, either in the Dashboard or through the admin bar that floats at the top of the page when signed in, create a new Poem.
- Fill in the title, then skip down to the "Original Document" field. Paste the xml for the poem in this field. When you save or publish the poem, it will automatically generate the post content for the poem.
- Fill in the "Author," "Difficulty," and "Type" fields. Author should be formatted: Lastname, Firstname.
- If the poem has any resources associated with it, add them in the "Resources" field. For media files, use the built-in "Add Media" button of the WYSIWYG editor.
- Hit "Publish."
- The home page of the site shows whichever poem has the category "Featured." If this category does not exist in your WP install, simple create it when creating a poem.
