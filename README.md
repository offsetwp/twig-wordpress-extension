![OffsetWP Twig WordPress Extension](https://raw.githubusercontent.com/offsetwp/art/refs/heads/main/cover/cover-twig-wordpress-extension-light.png#gh-light-mode-only)
![OffsetWP Twig WordPress Extension](https://raw.githubusercontent.com/offsetwp/art/refs/heads/main/cover/cover-twig-wordpress-extension-dark.png#gh-dark-mode-only)

<h1 align="center">
    OffsetWP Twig WordPress Extension
</h1>

<p align="center">
	The functions of WordPress, in Twig. Same names, same arguments, same result.
</p>

<br/>

- 🌿 `{{ the_permalink() }}`, `{{ wp_head() }}`, `{{ __('Read more', 'my-theme') }}`: WordPress, as a classic theme calls it
- 📚 356 functions: template tags, conditional tags, translation, escaping, addresses, media, menus, comments
- 🖨️ What a function prints is given back, so it can be kept, filtered and tested like any value
- 🏷️ Named arguments, as WordPress names them: `{{ the_title(before='<h1>', after='</h1>') }}`
- 🧩 `fn()` for every other function of WordPress, of a plugin or of a theme
- 🪶 One extension, and Twig as its only dependency

## Installation

**requirements:**
- PHP: 8.5+
- Twig: 3.27+
- WordPress: 6.5+

**command:**
```bash
composer require offsetwp/twig-wordpress-extension
```

**register the extension**, in a project built on [offsetwp/twig-bundle](https://github.com/offsetwp/twig-bundle):
```php
// config/packages/twig.php
use OffsetWP\Bundle\TwigBundle\Configuration\TwigConfig;
use OffsetWP\Twig\Extension\WordPressExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function ( ContainerConfigurator $container ): void {
	TwigConfig::create()
		->extension( WordPressExtension::class )
		->apply( $container );
};
```

Or in any environment of your own:

```php
$twig->addExtension( new \OffsetWP\Twig\Extension\WordPressExtension() );
```

The functions are those of WordPress itself, so a template calling them renders once WordPress
has loaded: from a theme, a plugin or a mu-plugin, which is where an OffsetWP kernel boots.

## Calling a function

```twig
<html {{ language_attributes() }}>
<head>
	<meta charset="{{ bloginfo('charset') }}">
	{{ wp_head() }}
</head>
<body {{ body_class('flex min-h-screen') }}>
	{{ wp_body_open() }}

	<a href="{{ esc_url(home_url('/')) }}" rel="home">{{ bloginfo('name') }}</a>
	{{ wp_nav_menu({ theme_location: 'primary', container: 'nav', fallback_cb: false }) }}

	{% for post in posts %}
		<h2><a href="{{ the_permalink(post) }}">{{ get_the_title(post) }}</a></h2>
		{{ get_the_post_thumbnail(post, 'large', { class: 'card__image' }) }}
	{% endfor %}

	{{ the_posts_pagination({ mid_size: 2 }) }}
	{{ wp_footer() }}
</body>
</html>
```

Each function keeps its name and its arguments, given in order or by the name WordPress gives
them. An argument left out keeps the default WordPress declares:

```twig
{{ the_title(before='<h1>', after='</h1>') }}
{{ get_the_post_thumbnail(post, attr={ class: 'wide' }) }}   {# the size stays 'post-thumbnail' #}
```

A function called for what it does rather than for what it gives is called with `do`, which
prints nothing:

```twig
{% do the_post() %}
{% do wp_reset_postdata() %}
```

## What a function gives back

A function that **returns** its result, such as `get_the_title()`, `home_url()` or `is_single()`,
is handed to Twig as it is. `{{ get_the_title(post) }}` compiles to a plain call of
`get_the_title()`, checked against the parameters WordPress declares. It is the same call a PHP
template makes, with no layer in between.

A function that **prints** its result, such as `the_permalink()`, `wp_head()` or `body_class()`,
is called the same way. What it prints is captured and given back instead of being left on the
output. It can then be kept, filtered and tested like any other value, and it lands where the
template calls it, whether or not the environment yields:

```twig
{% set link = the_permalink(post) %}
{{ the_title()|upper }}
{% set widgets = dynamic_sidebar('footer') %}
{% if widgets %}<aside>{{ widgets|raw }}</aside>{% endif %}
```

A value kept with `set` is a variable from then on, and Twig escapes a variable when it prints it,
as the `autoescape` option says. Markup printed through a variable takes `|raw`. Markup printed
by calling the function directly does not need it.

Some functions both print and return, and these rules cover them:

| The function | Gives back |
|---|---|
| prints, and returns nothing | what it printed |
| prints, and returns the same markup: `wp_link_pages()`, `selected()` | that markup, once |
| prints, and returns a boolean: `dynamic_sidebar()` | what it printed, or `false` when it printed nothing |
| is told not to print: `wp_nav_menu({ echo: false })` | what it returned |
| prints a notice on its way to a value, as a getter does while debugging | the value; the notice goes to the output, where PHP would have put it |

### Escaping

Every function is marked safe for HTML. What WordPress gives is printed as WordPress gives it,
and Twig does not escape it a second time: `{{ __('A & B') }}` prints `A & B`, exactly as
`echo __( 'A & B' );` does.

A template escapes where a classic theme would, with the functions WordPress has for it:

```twig
<a href="{{ esc_url(home_url('/')) }}">{{ esc_html__('Home', 'my-theme') }}</a>
<p>{{ esc_html(get_post_meta(post.ID, 'subtitle', true)) }}</p>
```

Anything else a template prints, such as a variable or a property like `post.post_title`, is
escaped or not according to the `autoescape` option of the environment. This extension marks its
own functions and nothing else.

## fn()

The list holds the functions a theme calls. Any other function of WordPress, of a plugin or of a
theme can be called by its name:

```twig
{{ fn('yoast_breadcrumb', '<nav class="breadcrumb">', '</nav>') }}
{{ fn('the_field', 'subtitle') }}
{% set price = fn('wc_price', product.price, args={ decimals: 0 }) %}
```

It gives back what the function prints, or what it returns, exactly like the functions of the
list. It takes named arguments too, and its result is marked safe for HTML as well.

It calls functions, and only those declared in PHP code: WordPress, its plugins, its themes, a
Composer package. A function of PHP itself is refused, and so is a method:

```
fn() calls the functions of WordPress, of its plugins and of its themes, and "exec" is a function of PHP itself. Twig has filters and functions of its own for what PHP does.
```

## The functions

Grouped the way a theme reaches for them. Each group lists the functions that print first,
whose output is given back, then the functions that return.

### The document

**Print:** `wp_head` `wp_footer` `wp_body_open` `language_attributes` `body_class` `bloginfo` `site_icon_url` `the_custom_logo` `header_image` `the_custom_header_markup`

**Return:** `get_language_attributes` `get_body_class` `get_bloginfo` `wp_get_document_title` `get_locale` `is_rtl` `get_site_icon_url` `has_site_icon` `get_custom_logo` `has_custom_logo` `get_header_image` `has_header_image` `get_custom_header_markup` `display_header_text` `get_header_textcolor` `get_background_image` `get_background_color`

### Translation

**Print:** `_e` `_ex` `esc_html_e` `esc_attr_e`

**Return:** `__` `_x` `_n` `_nx` `esc_html__` `esc_html_x` `esc_attr__` `esc_attr_x`

### Escaping and formatting

**Return:** `esc_html` `esc_attr` `esc_url` `esc_js` `esc_textarea` `esc_xml` `wp_kses` `wp_kses_post` `wp_kses_data` `sanitize_title` `sanitize_html_class` `wp_strip_all_tags` `wp_trim_words` `wp_html_excerpt` `wpautop` `wptexturize` `make_clickable` `antispambot` `wp_unique_id` `number_format_i18n` `size_format` `human_time_diff` `date_i18n` `wp_date` `current_time`

### Hooks, shortcodes and blocks

**Print:** `do_action`

**Return:** `apply_filters` `do_shortcode` `strip_shortcodes` `has_shortcode` `shortcode_exists` `do_blocks` `has_block` `has_blocks`

### Addresses

**Print:** `the_privacy_policy_link`

**Return:** `home_url` `site_url` `get_home_url` `get_site_url` `admin_url` `rest_url` `get_theme_file_uri` `get_parent_theme_file_uri` `get_template_directory_uri` `get_stylesheet_directory_uri` `get_stylesheet_uri` `get_privacy_policy_url` `get_the_privacy_policy_link` `get_search_link` `get_post_type_archive_link` `get_author_posts_url` `get_day_link` `get_month_link` `get_year_link` `get_feed_link` `add_query_arg` `remove_query_arg` `wp_nonce_url`

### The query and the loop

**Return:** `have_posts` `the_post` `rewind_posts` `in_the_loop` `setup_postdata` `wp_reset_postdata` `wp_reset_query` `is_main_query` `get_query_var` `get_queried_object` `get_queried_object_id` `get_post` `get_posts` `get_pages` `get_page_by_path` `get_post_field` `get_post_meta` `get_post_type` `get_post_type_object` `get_post_format` `has_post_format` `get_post_ancestors` `wp_get_post_parent_id` `get_previous_post` `get_next_post`

### The post

**Print:** `the_ID` `the_title` `the_title_attribute` `the_permalink` `the_content` `the_excerpt` `post_class` `the_date` `the_time` `the_modified_date` `the_modified_time` `the_author` `the_author_meta` `the_author_link` `the_author_posts_link` `wp_link_pages` `edit_post_link`

**Return:** `get_the_ID` `get_the_title` `get_permalink` `get_the_permalink` `get_the_content` `get_the_excerpt` `has_excerpt` `get_post_class` `get_the_date` `get_the_time` `get_the_modified_date` `get_the_modified_time` `get_post_time` `get_post_modified_time` `get_the_author` `get_the_author_meta` `get_the_author_link` `get_the_author_posts_link` `is_sticky` `post_password_required` `get_the_password_form` `get_edit_post_link` `get_page_template_slug`

### Featured image and media

**Print:** `the_post_thumbnail` `the_post_thumbnail_url` `the_post_thumbnail_caption`

**Return:** `has_post_thumbnail` `get_post_thumbnail_id` `get_the_post_thumbnail` `get_the_post_thumbnail_url` `get_the_post_thumbnail_caption` `wp_get_attachment_image` `wp_get_attachment_image_src` `wp_get_attachment_image_url` `wp_get_attachment_image_srcset` `wp_get_attachment_image_sizes` `wp_get_attachment_url` `wp_get_attachment_caption` `wp_get_attachment_metadata` `wp_get_attachment_link` `wp_attachment_is_image` `wp_oembed_get`

### Categories, tags and terms

**Print:** `the_category` `the_tags` `the_terms` `single_cat_title` `single_tag_title` `single_term_title` `wp_list_categories` `wp_dropdown_categories` `wp_tag_cloud` `edit_term_link`

**Return:** `get_the_category` `get_the_category_list` `get_the_tags` `get_the_tag_list` `get_the_terms` `get_the_term_list` `wp_get_post_terms` `has_category` `has_tag` `has_term` `in_category` `get_term` `get_terms` `get_term_by` `get_categories` `get_tags` `get_term_link` `get_category_link` `get_tag_link` `get_term_meta` `term_description` `category_description` `tag_description`

### Archives and pagination

**Print:** `the_archive_title` `the_archive_description` `single_post_title` `post_type_archive_title` `single_month_title` `wp_get_archives` `get_calendar` `the_posts_pagination` `the_posts_navigation` `posts_nav_link` `next_posts_link` `previous_posts_link` `the_post_navigation` `next_post_link` `previous_post_link`

**Return:** `get_the_archive_title` `get_the_archive_description` `get_the_posts_pagination` `get_the_posts_navigation` `paginate_links` `get_next_posts_link` `get_previous_posts_link` `get_the_post_navigation` `get_next_post_link` `get_previous_post_link`

### Menus

**Print:** `wp_nav_menu` `wp_list_pages` `wp_page_menu`

**Return:** `has_nav_menu` `wp_get_nav_menu_items` `wp_get_nav_menu_object` `get_nav_menu_locations` `wp_get_nav_menu_name`

### Sidebars, template parts and search

**Print:** `dynamic_sidebar` `the_widget` `get_template_part` `get_search_form` `the_search_query`

**Return:** `is_active_sidebar` `get_search_query`

### Comments

**Print:** `comments_template` `comments_number` `wp_list_comments` `comment_form` `comment_class` `comment_author` `comment_author_link` `comment_text` `comment_date` `comment_time` `comment_reply_link` `cancel_comment_reply_link` `comments_popup_link` `edit_comment_link` `the_comments_navigation` `the_comments_pagination` `paginate_comments_links` `previous_comments_link` `next_comments_link`

**Return:** `comments_open` `pings_open` `have_comments` `get_comments` `get_comments_number` `get_comments_number_text` `get_comments_link` `get_comment_link` `get_comment_class` `get_comment_author` `get_comment_author_link` `get_comment_text` `get_comment_date` `get_comment_time` `get_comment_reply_link` `get_edit_comment_link` `get_the_comments_navigation` `get_the_comments_pagination`

### Users, login and forms

**Print:** `wp_loginout` `wp_register` `wp_login_form` `wp_nonce_field` `selected` `checked` `disabled`

**Return:** `is_user_logged_in` `current_user_can` `get_current_user_id` `wp_get_current_user` `get_userdata` `get_user_by` `get_user_meta` `is_super_admin` `is_multi_author` `get_avatar` `get_avatar_url` `wp_login_url` `wp_logout_url` `wp_registration_url` `wp_lostpassword_url` `wp_create_nonce`

### Conditional tags

**Return:** `is_home` `is_front_page` `is_single` `is_singular` `is_page` `is_page_template` `is_category` `is_tag` `is_tax` `is_author` `is_date` `is_year` `is_month` `is_day` `is_time` `is_archive` `is_search` `is_404` `is_attachment` `is_post_type_archive` `is_privacy_policy` `is_paged` `is_preview` `is_feed` `is_embed` `is_new_day` `is_admin` `is_customize_preview` `is_multisite` `is_main_site` `is_child_theme` `is_ssl` `wp_is_mobile` `current_theme_supports` `post_type_exists` `taxonomy_exists` `is_post_type_hierarchical` `is_post_type_viewable`

### Settings and environment

**Return:** `get_option` `get_theme_mod` `wp_get_theme` `wp_get_environment_type`

## Troubleshooting

### Unknown "…" function

The function is not in the list. Call it with fn(), or open an issue to add it. Some functions
are left out on purpose, and fn() can still call them:

- `get_header()`, `get_footer()` and `get_sidebar()` load header.php, footer.php and sidebar.php,
  which a Twig theme does not have;
- `wp_title()` was replaced by the `title-tag` theme support;
- `wp_enqueue_script()` and `wp_enqueue_style()` belong in PHP, before the head is printed;
- `is_plugin_active()` lives in wp-admin, and is not loaded on the front of the site;
- deprecated functions are left out too.

### The function "…" does not exist

A template called a function of the list that WordPress had not declared. The template ran before
WordPress loaded, or without it, from a command-line script for example. Render templates from a
theme, a plugin or a mu-plugin, once WordPress has loaded.

A function that returns, and that WordPress has not declared yet when Twig first asks this
extension for its functions, is looked up again each time a template calls it. This covers the
pluggable functions, which WordPress declares after the plugins: it answers as soon as WordPress
has declared it.

### A callback that leaves an output buffer open

A function that prints runs inside an output buffer of its own, and every buffer opened above it
is closed when the function returns. Take a callback hooked to `wp_head` that opens a buffer meant
to be closed by another hook much later. Its buffer is closed early, and its content is given
back with what `wp_head()` printed.

## Licence

MIT. See [LICENSE](LICENSE).
