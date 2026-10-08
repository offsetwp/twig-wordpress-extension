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
- 📚 668 functions: template tags, template parts, conditional tags, translation, escaping, addresses, media, menus, comments, blocks, scripts and styles
- 🖨️ What a function prints is given back, so it can be kept, filtered and tested like any value
- 🏷️ Named arguments, as WordPress names them: `{{ the_title(before='<h1>', after='</h1>') }}`
- 🧩 `fn()` for every other function of WordPress, of a plugin or of a theme
- 🧭 `site`, `theme` and `user` in every template: `{{ site.name }}`, `{{ theme.url }}`, `{% if user.logged_in %}`
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

## Usage

A template of a theme calls the functions of WordPress under their own names, and reads the site,
the theme and the user who is logged in from three variables. What belongs to the page, its posts
here, comes from the file of the theme that renders it:

```php
// index.php, in the theme
twig()->display( 'index.twig', array( 'posts' => $wp_query->posts ) );
```

```twig
<html {{ language_attributes() }}>
	<head>
		<meta charset="{{ site.charset }}">

		{{ wp_head() }}
	</head>
	<body {{ body_class('flex min-h-screen') }}>
		{{ wp_body_open() }}

		<a href="{{ site.url('/') }}" rel="home">
			<img src="{{ theme.url }}/logo.svg" alt="">
			{{ site.name }}
		</a>

		{% if user.logged_in %}
			{{ esc_html__('Hello', 'my-theme') }} {{ user.name }}
		{% endif %}

		{{ wp_nav_menu({ theme_location: 'primary' }) }}

		{{ fn('yoast_breadcrumb') }}

		{% for post in posts %}
			<h2>
				<a href="{{ the_permalink(post) }}">
					{{ get_the_title(post) }}
				</a>
			</h2>

			{{ get_the_post_thumbnail(post, attr={ class: 'card__image' }) }}
		{% endfor %}

		{{ the_posts_pagination({ mid_size: 2 }) }}

		{{ wp_footer() }}
	</body>
</html>
```

The sections below tell each part: how a function is called and what it gives back, `fn()` for
the functions the list does not hold, and the variables.

## Calling a function

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

A theme that keeps a header.php and a footer.php loads them the way a classic theme does.
WordPress fires the `get_header` and `get_footer` actions and includes the file, and what the
file prints lands where the template calls it:

```twig
{{ get_header() }}
<main>{% block content %}{% endblock %}</main>
{{ get_footer() }}
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

## The variables

Three variables are there in every template: `site`, `theme` and `user`.

```twig
<header>
	<a href="{{ site.url }}" rel="home">{{ site.name }}</a>
	{% if site.description %}<p>{{ site.description }}</p>{% endif %}

	{% if user.logged_in %}
		<img src="{{ user.avatar(32) }}" alt=""> {{ user.name }}
	{% else %}
		<a href="{{ wp_login_url() }}">{{ esc_html__('Log in', 'my-theme') }}</a>
	{% endif %}
</header>

<link rel="stylesheet" href="{{ theme.url }}/style.css?ver={{ theme.version }}">
```

Each one is an object, and each of its methods is one call of WordPress, made when a template
reads it. Twig builds the variables of an extension once, on the first render, and these ask
nothing of WordPress until a template reads them.

What belongs to a page, its post or its posts, is not among them: a variable of an extension is
the same for every template an environment renders. The file of the theme that renders a template
passes it, as `posts` in the [usage](#usage).

### site

| In a template | In WordPress |
|---|---|
| `site.name` | `get_bloginfo('name')` |
| `site.description` | `get_bloginfo('description')` |
| `site.url`, `site.url('/blog')` | `home_url()`, `home_url('/blog')` |
| `site.locale` | `get_locale()`, such as `fr_FR` |
| `site.language` | `get_bloginfo('language')`, such as `fr-FR`, the form the `lang` attribute takes |
| `site.charset` | `get_bloginfo('charset')` |
| `site.option('date_format')` | `get_option('date_format')` |

Whatever else `get_bloginfo()` knows, the address of a feed say, is one function away:
`{{ get_bloginfo('rss2_url') }}`.

### theme

The active theme, the one `wp_get_theme()` gives:

| In a template | In WordPress |
|---|---|
| `theme.name` | the `Name` header of its `style.css` |
| `theme.version` | the `Version` header of its `style.css` |
| `theme.slug` | the name of its directory: `get_stylesheet()` |
| `theme.url` | the address of its directory: `get_stylesheet_directory_uri()` |
| `theme.parent` | the parent theme of a child theme, as a `theme` of its own, or `null` |
| `theme.get('TextDomain')` | any header of its `style.css`: `wp_get_theme()->get('TextDomain')` |

Without a child theme, `theme.url` is also `get_template_directory_uri()`. With one, it is the
address of the child theme, and `theme.parent.url` the address of the parent. A file a child
theme may or may not override is found by `get_theme_file_uri('…')`, which looks in both.

### user

The user who is logged in, the one `wp_get_current_user()` gives:

| In a template | In WordPress |
|---|---|
| `user.logged_in` | whether anybody is: `wp_get_current_user()->exists()` |
| `user.id` | `ID`, which is `0` when nobody is logged in |
| `user.name` | `display_name` |
| `user.email` | `user_email` |
| `user.roles` | `roles`, a list of slugs |
| `user.can('edit_posts')` | `user_can($user, 'edit_posts')` |
| `user.avatar`, `user.avatar(32)` | `get_avatar_url($user, ['size' => 32])`, at 96 pixels unless told otherwise |
| `user.link` | `get_author_posts_url($user->ID)` |
| `user.meta('phone')` | `get_user_meta($user->ID, 'phone', true)` |

Nobody logged in, WordPress gives the user of ID 0, and so does `user`. `{% if user %}` always
holds, and `{% if user.logged_in %}` is the question to ask. A function of WordPress that wants a
user is given its ID: `{{ get_avatar(user.id, 32) }}`.

### What they print

WordPress keeps some text encoded already: the name and the tagline of the site, the name of a
theme, the display name of a user. `site.name`, `site.description`, `theme.name` and `user.name`
hand that text over marked safe, so that it is printed once, as WordPress prints it: `L'Atelier`,
and not `L&#039;Atelier`. Empty, such a text is an empty string, which a condition reads as false.

Everything else comes as WordPress gives it, and is printed as the `autoescape` option says, like
any value: an address, an option, a meta, a role. `{{ site.option('footer_text') }}` is escaped.

### Adding to them

The variables are three classes, `Site`, `Theme` and `User`, in
`OffsetWP\Twig\Extension\WordPressExtension\Context`, and none of them is final. A project extends
one with what it needs, and registers its own class under the same name: a global of the
environment takes the place of a variable of an extension, and a variable passed to a template
takes the place of both.

```php
namespace App\Twig;

use OffsetWP\Twig\Extension\WordPressExtension\Context\Site as WordPressSite;

class Site extends WordPressSite {
	public function phone(): string {
		return (string) get_option( 'business_phone' );
	}
}
```

```php
// config/packages/twig.php, with App\Twig\Site declared as a service
TwigConfig::create()
	->extension( WordPressExtension::class )
	->globalService( 'site', \App\Twig\Site::class )
	->apply( $container );
```

`{{ site.phone }}` reads the option, and `{{ site.name }}` is still the name of the site. A class
that extends `Theme` or `User` reaches the `WP_Theme` or the `WP_User` behind it with
`$this->model()`.

## The functions

Grouped the way a theme reaches for them. Each group lists the functions that print first,
whose output is given back, then the functions that return.

### The document

**Print:** `wp_head` `wp_footer` `wp_body_open` `language_attributes` `body_class` `bloginfo` `wp_title` `site_icon_url` `the_custom_logo` `header_image` `the_header_image_tag` `the_custom_header_markup` `the_header_video_url` `header_textcolor` `background_image` `background_color`

**Return:** `get_language_attributes` `get_body_class` `get_bloginfo` `wp_get_document_title` `get_locale` `is_rtl` `get_site_icon_url` `has_site_icon` `get_custom_logo` `has_custom_logo` `get_header_image` `has_header_image` `get_header_image_tag` `get_custom_header` `has_custom_header` `get_custom_header_markup` `has_header_video` `is_header_video_active` `get_header_video_url` `display_header_text` `get_header_textcolor` `get_background_image` `get_background_color`

### Translation

**Print:** `_e` `_ex` `esc_html_e` `esc_attr_e`

**Return:** `__` `_x` `_n` `_nx` `esc_html__` `esc_html_x` `esc_attr__` `esc_attr_x` `determine_locale` `get_user_locale` `wp_get_list_item_separator`

### Escaping and formatting

**Return:** `esc_html` `esc_attr` `esc_url` `esc_url_raw` `esc_js` `esc_textarea` `esc_xml` `tag_escape` `wp_kses` `wp_kses_post` `wp_kses_data` `wp_kses_one_attr` `wp_kses_allowed_html` `safecss_filter_attr` `sanitize_title` `sanitize_title_with_dashes` `sanitize_html_class` `sanitize_key` `sanitize_text_field` `sanitize_textarea_field` `sanitize_email` `sanitize_hex_color` `sanitize_hex_color_no_hash` `wp_strip_all_tags` `wp_trim_words` `wp_html_excerpt` `wp_trim_excerpt` `excerpt_remove_blocks` `get_url_in_content` `wpautop` `shortcode_unautop` `force_balance_tags` `wptexturize` `convert_smilies` `wp_specialchars_decode` `remove_accents` `make_clickable` `wp_rel_nofollow` `wp_rel_ugc` `links_add_target` `url_shorten` `wp_make_link_relative` `trailingslashit` `untrailingslashit` `user_trailingslashit` `wp_basename` `antispambot` `wp_unique_id` `wp_unique_prefixed_id` `number_format_i18n` `wp_sprintf` `size_format` `human_time_diff` `human_readable_duration` `date_i18n` `wp_date` `current_time` `current_datetime` `wp_timezone` `wp_timezone_string` `mysql2date` `mysql_to_rfc3339` `get_date_from_gmt` `get_gmt_from_date`

### Hooks, shortcodes and blocks

**Print:** `do_action`

**Return:** `apply_filters` `did_action` `did_filter` `doing_action` `doing_filter` `has_action` `has_filter` `do_shortcode` `apply_shortcodes` `strip_shortcodes` `has_shortcode` `shortcode_exists` `do_blocks` `has_block` `has_blocks` `parse_blocks` `render_block` `serialize_block` `serialize_blocks` `get_block_wrapper_attributes` `wp_interactivity_state` `wp_interactivity_config` `wp_interactivity_data_wp_context` `wp_interactivity_process_directives`

### Scripts and styles

A script or a style a template queues once `wp_head()` has run is printed by `wp_footer()`, as
it would be from PHP. What is added to a script or a style already printed, with
`wp_add_inline_script()`, `wp_add_inline_style()` or `wp_localize_script()`, is not printed at
all.

**Print:** `wp_print_script_tag` `wp_print_inline_script_tag`

**Return:** `wp_enqueue_script` `wp_enqueue_script_module` `wp_enqueue_style` `wp_add_inline_script` `wp_add_inline_style` `wp_localize_script` `wp_script_is` `wp_style_is` `wp_get_script_tag` `wp_get_inline_script_tag`

### Addresses

**Print:** `the_privacy_policy_link` `the_feed_link` `post_comments_feed_link` `the_shortlink`

**Return:** `home_url` `site_url` `get_home_url` `get_site_url` `network_home_url` `network_site_url` `set_url_scheme` `admin_url` `get_admin_url` `network_admin_url` `get_dashboard_url` `get_edit_profile_url` `get_edit_user_link` `get_delete_post_link` `rest_url` `get_rest_url` `includes_url` `content_url` `plugins_url` `wp_get_upload_dir` `get_theme_file_uri` `get_parent_theme_file_uri` `get_template_directory_uri` `get_stylesheet_directory_uri` `get_stylesheet_uri` `get_privacy_policy_url` `get_the_privacy_policy_link` `get_search_link` `get_search_feed_link` `get_search_comments_feed_link` `get_post_type_archive_link` `get_post_type_archive_feed_link` `get_author_posts_url` `get_author_feed_link` `get_page_link` `get_post_permalink` `get_attachment_link` `get_page_uri` `url_to_postid` `get_day_link` `get_month_link` `get_year_link` `get_feed_link` `get_post_comments_feed_link` `get_category_feed_link` `get_tag_feed_link` `get_term_feed_link` `add_query_arg` `remove_query_arg` `wp_nonce_url` `wp_get_shortlink` `wp_get_canonical_url` `wp_get_referer` `wp_is_internal_link` `get_sitemap_url`

### The query and the loop

**Return:** `have_posts` `the_post` `rewind_posts` `in_the_loop` `setup_postdata` `wp_reset_postdata` `wp_reset_query` `is_main_query` `get_query_var` `get_queried_object` `get_queried_object_id` `get_post` `get_posts` `wp_get_recent_posts` `get_children` `wp_count_posts` `get_pages` `get_page_by_path` `get_post_field` `get_post_status` `get_post_mime_type` `get_extended` `get_post_meta` `metadata_exists` `get_post_custom` `get_post_custom_keys` `get_post_custom_values` `get_post_type` `get_post_type_object` `get_post_types` `post_type_supports` `get_post_format` `has_post_format` `get_post_format_string` `get_post_format_link` `get_post_format_strings` `get_post_ancestors` `wp_get_post_parent_id` `get_post_parent` `has_post_parent` `get_previous_post` `get_next_post` `get_adjacent_post` `get_boundary_post` `get_lastpostdate` `get_lastpostmodified`

### The post

**Print:** `the_ID` `the_guid` `the_title` `the_title_attribute` `the_permalink` `the_content` `the_excerpt` `post_class` `the_date` `the_time` `the_modified_date` `the_modified_time` `the_author` `the_modified_author` `the_author_meta` `the_author_link` `the_author_posts_link` `the_author_posts` `wp_link_pages` `edit_post_link`

**Return:** `get_the_ID` `get_the_guid` `get_the_title` `get_permalink` `get_the_permalink` `get_the_content` `get_the_excerpt` `has_excerpt` `get_post_class` `get_the_date` `get_the_time` `get_the_modified_date` `get_the_modified_time` `get_post_time` `get_post_modified_time` `get_post_datetime` `get_post_timestamp` `get_the_author` `get_the_modified_author` `get_the_author_meta` `get_the_author_link` `get_the_author_posts_link` `get_the_author_posts` `is_sticky` `post_password_required` `get_the_password_form` `get_edit_post_link` `get_page_template_slug`

### Featured image and media

**Print:** `the_post_thumbnail` `the_post_thumbnail_url` `the_post_thumbnail_caption` `the_attachment_link` `previous_image_link` `next_image_link` `adjacent_image_link`

**Return:** `has_post_thumbnail` `get_post_thumbnail_id` `get_the_post_thumbnail` `get_the_post_thumbnail_url` `get_the_post_thumbnail_caption` `wp_get_attachment_image` `wp_get_attachment_image_src` `wp_get_attachment_image_url` `wp_get_attachment_thumb_url` `wp_get_original_image_url` `wp_get_attachment_image_srcset` `wp_get_attachment_image_sizes` `wp_get_loading_optimization_attributes` `wp_filter_content_tags` `wp_get_attachment_url` `attachment_url_to_postid` `wp_get_attachment_caption` `wp_get_attachment_metadata` `wp_get_attachment_link` `wp_attachment_is_image` `wp_attachment_is` `wp_mime_type_icon` `get_attached_media` `get_media_embedded_in_content` `get_post_gallery` `get_post_galleries` `get_post_gallery_images` `get_post_galleries_images` `get_previous_image_link` `get_next_image_link` `get_adjacent_image_link` `gallery_shortcode` `img_caption_shortcode` `wp_audio_shortcode` `wp_video_shortcode` `wp_playlist_shortcode` `wp_oembed_get` `get_post_embed_html` `get_post_embed_url`

### Categories, tags and terms

**Print:** `the_category` `the_tags` `the_terms` `the_taxonomies` `single_cat_title` `single_tag_title` `single_term_title` `wp_list_categories` `wp_dropdown_categories` `wp_tag_cloud` `edit_term_link` `edit_tag_link`

**Return:** `get_the_category` `get_the_category_list` `get_the_category_by_ID` `get_category_parents` `get_the_tags` `get_the_tag_list` `get_the_terms` `get_the_term_list` `get_term_parents_list` `get_the_taxonomies` `wp_get_post_terms` `wp_get_post_categories` `wp_get_post_tags` `wp_get_object_terms` `has_category` `has_tag` `has_term` `is_object_in_term` `is_object_in_taxonomy` `in_category` `cat_is_ancestor_of` `term_is_ancestor_of` `term_exists` `get_term` `get_terms` `get_term_by` `get_term_field` `get_term_children` `get_ancestors` `wp_count_terms` `get_categories` `get_category` `get_category_by_slug` `get_cat_name` `get_cat_ID` `get_tags` `get_tag` `get_term_link` `get_category_link` `get_tag_link` `get_term_meta` `get_taxonomies` `get_taxonomy` `get_object_taxonomies` `get_post_taxonomies` `term_description` `category_description` `tag_description` `wp_generate_tag_cloud` `get_edit_term_link` `get_edit_tag_link`

### Archives and pagination

**Print:** `the_archive_title` `the_archive_description` `single_post_title` `post_type_archive_title` `single_month_title` `wp_get_archives` `get_calendar` `the_posts_pagination` `the_posts_navigation` `posts_nav_link` `next_posts_link` `previous_posts_link` `next_posts` `previous_posts` `the_post_navigation` `next_post_link` `previous_post_link` `adjacent_post_link`

**Return:** `get_the_archive_title` `get_the_archive_description` `get_the_post_type_description` `get_archives_link` `get_the_posts_pagination` `get_the_posts_navigation` `paginate_links` `get_posts_nav_link` `get_next_posts_link` `get_previous_posts_link` `get_next_posts_page_link` `get_previous_posts_page_link` `get_pagenum_link` `get_the_post_navigation` `get_next_post_link` `get_previous_post_link` `get_adjacent_post_link`

### Menus

**Print:** `wp_nav_menu` `wp_list_pages` `wp_page_menu` `wp_dropdown_pages` `wp_list_bookmarks`

**Return:** `has_nav_menu` `wp_get_nav_menu_items` `wp_get_nav_menu_object` `wp_get_nav_menus` `is_nav_menu` `get_nav_menu_locations` `get_registered_nav_menus` `wp_get_nav_menu_name` `get_bookmarks` `get_bookmark`

### Sidebars, template parts and search

**Print:** `dynamic_sidebar` `the_widget` `wp_meta` `get_header` `get_footer` `get_sidebar` `get_template_part` `block_template_part` `block_header_area` `block_footer_area` `get_search_form` `the_search_query`

**Return:** `is_active_sidebar` `is_dynamic_sidebar` `is_registered_sidebar` `wp_get_sidebar` `is_active_widget` `get_search_query`

### Comments

**Print:** `comments_template` `comments_number` `comments_link` `wp_list_comments` `comment_form` `comment_form_title` `comment_id_fields` `comment_class` `comment_ID` `comment_author` `comment_author_link` `comment_author_email` `comment_author_email_link` `comment_author_url` `comment_author_url_link` `comment_text` `comment_excerpt` `comment_date` `comment_time` `comment_type` `comment_reply_link` `post_reply_link` `cancel_comment_reply_link` `comments_popup_link` `edit_comment_link` `the_comments_navigation` `the_comments_pagination` `paginate_comments_links` `previous_comments_link` `next_comments_link` `trackback_url`

**Return:** `comments_open` `pings_open` `have_comments` `the_comment` `get_comments` `get_comment` `get_approved_comments` `get_comment_count` `wp_count_comments` `get_comment_meta` `wp_get_comment_status` `get_comments_number` `get_comments_number_text` `get_comments_link` `get_comment_link` `get_page_of_comment` `get_comment_id_fields` `wp_get_current_commenter` `wp_get_unapproved_comment_author_email` `get_comment_class` `get_comment_ID` `get_comment_author` `get_comment_author_link` `get_comment_author_email` `get_comment_author_email_link` `get_comment_author_url` `get_comment_author_url_link` `get_comment_text` `get_comment_excerpt` `get_comment_date` `get_comment_time` `get_comment_type` `get_comment_reply_link` `get_post_reply_link` `get_cancel_comment_reply_link` `get_edit_comment_link` `get_the_comments_navigation` `get_the_comments_pagination` `get_comment_pages_count` `get_comments_pagenum_link` `get_previous_comments_link` `get_next_comments_link` `get_trackback_url`

### Users, login and forms

**Print:** `wp_list_authors` `wp_list_users` `wp_dropdown_users` `wp_loginout` `wp_register` `wp_login_form` `wp_nonce_field` `wp_referer_field` `wp_original_referer_field` `selected` `checked` `disabled` `wp_readonly`

**Return:** `is_user_logged_in` `current_user_can` `user_can` `author_can` `get_current_user_id` `wp_get_current_user` `get_userdata` `get_user_by` `get_user_meta` `get_users` `count_user_posts` `count_many_users_posts` `is_super_admin` `is_multi_author` `get_avatar` `get_avatar_url` `get_avatar_data` `wp_login_url` `wp_logout_url` `wp_registration_url` `wp_lostpassword_url` `wp_create_nonce` `wp_required_field_indicator` `wp_required_field_message`

### Conditional tags

**Return:** `is_home` `is_front_page` `is_single` `is_singular` `is_page` `is_page_template` `is_category` `is_tag` `is_tax` `is_author` `is_date` `is_year` `is_month` `is_day` `is_time` `is_archive` `is_search` `is_404` `is_attachment` `is_post_type_archive` `is_privacy_policy` `is_paged` `is_preview` `is_feed` `is_comment_feed` `is_trackback` `is_embed` `is_robots` `is_favicon` `is_new_day` `is_admin` `is_login` `is_admin_bar_showing` `wp_doing_ajax` `is_customize_preview` `is_multisite` `is_main_site` `is_child_theme` `wp_is_block_theme` `wp_theme_has_theme_json` `is_ssl` `wp_is_mobile` `current_theme_supports` `post_type_exists` `taxonomy_exists` `is_post_type_hierarchical` `is_post_type_viewable` `is_post_status_viewable` `is_post_publicly_viewable` `is_taxonomy_hierarchical` `is_taxonomy_viewable` `is_term_publicly_viewable` `is_wp_error`

### Settings and environment

**Return:** `get_option` `get_site_option` `get_theme_mod` `get_theme_mods` `get_theme_support` `wp_get_theme` `get_template` `get_stylesheet` `get_template_directory` `get_stylesheet_directory` `get_theme_file_path` `get_parent_theme_file_path` `wp_get_global_settings` `wp_get_global_styles` `wp_get_global_stylesheet` `wp_get_environment_type` `wp_get_development_mode` `wp_is_development_mode` `get_current_blog_id`

## Troubleshooting

### Unknown "…" function

The function is not in the list. Call it with fn(), or open an issue to add it. Some functions
are left out on purpose, and fn() can still call them:

- the functions that set WordPress up rather than render a page with it, such as
  `add_action()`, `register_post_type()` or `update_option()`, belong in PHP;
- the callbacks WordPress hooks to `wp_head` and `wp_footer` itself, such as `feed_links()` or
  `rel_canonical()`, are printed by `wp_head()` and `wp_footer()` already;
- the tags the feed and embed templates of WordPress call, such as `the_title_rss()` or
  `the_excerpt_embed()`, serve those templates; the links to a feed and the embed code of a
  post are in the list;
- `locate_template()` prints the template it loads and returns its path in the same call, and a
  template could be given only one of them: `get_template_part()` loads a template part and
  gives back what it prints;
- the helpers Twig already has under another name: `wp_parse_args()` is the `merge` filter,
  `wp_list_pluck()` is `column`, and `wp_json_encode()` is `json_encode`;
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
back with what `wp_head()` printed. The same goes for a callback hooked to the `get_header`
action, which `get_header()` fires: a buffer it opens to rewrite the whole page is closed when
`get_header()` returns.

## Licence

MIT. See [LICENSE](LICENSE).
