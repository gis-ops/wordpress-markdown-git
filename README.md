# WordPress Plugin - Publish Documents from Git

This WordPress Plugin lets you easily publish, collaborate on and version control your \[**Markdown, Jupyter notebook**\] documents directly from your favorite remote Git platform, **even if it's self-hosted**.

The advantages are:

- Write documents in your favorite editor and just push to your remote repository to update your blog instantly
- Use the power of version control: publish different versions of the document in different posts, i.e. from another branch or commit than latest `master`
- Easy to update by your readers via pull requests, minimizing the chance of stale tutorials

The following document types are currently supported:

- Markdown
- Jupyter notebooks (**only for public repositories**)

The following platforms are currently supported:

- Github
- Bitbucket
- Gitlab

## Usage

**Note**, this plugin uses Github's wonderful [`/markdown` API](https://developer.github.com/v3/markdown/) to render to HTML. This comes with 2 caveats:

1. Unless authenticated, the rate limit is set at 60 requests per minute. Since v1.1.0 the plugin is capable of statically [caching content](#caching). In case that's not dynamic enough for you, it's **strongly recommended** to create a Github access token and register it with the plugin. Then the rate limit will be set to 5000 requests per hour. See [Global attributes section](#global-attributes) for details on how to do that.
2. The Markdown content cannot exceed 400 KB, so roughly 400 000 characters incl whitespace. If not a monographic dissertation, this should not be an applicable limit though.

### Shortcodes

The plugin features a variety of shortcodes.

#### Publish documents

The document-specific shortcodes follow a pattern of `[git-<platform>-<action>]`, where

- `<platform>` can be one of
    - `github`: if you use Github as your VCS platform
    - `bitbucket`: if you use Bitbucket as your VCS platform
    - `gitlab`: if you use Gitlab as your VCS platform
- `<action>` can be one of
    - `markdown`: Render your Markdown files hosted on your VCS platform in Github's rendering style
    - `jupyter`: Render your Jupyter notebook hosted on your VCS platfrom (**only for public repositories**)
    - `checkout`: Renders a small badge-like box with a link to the document and the date of the last commit
    - `history`:  Renders a `<h2>` section with the last commit dates, messages and authors

#### Manipulate rendering style

Additionally, there's an enclosing shortcode `[git-add-css]` which adds a `<div id="git-add-css" class="<classes_attribute>"` to wrap its contents. That way you can manipulate the style freely with additional CSS classes. Follow these steps:

1. Add a CSS file to your theme's root folder, which contains some classes, e.g. `class1`, `class2`, `class3`
2. Enqueue the CSS file by adding `wp_enqueue_style('my-style', get_template_directory_uri().'/my-style.css');` to the theme's `functions.php`
3. Add the enclosing `git-add-css` shortcode to your post with the custom CSS classes in the `classes` attribute, e.g.:

```
[git-add-css classes="class1 class2 class3"]
    [git-gitlab-checkout url=...]
    [git-gitlab-markdown url=...]
    [git-gitlab-history url=...]
[/git-add-css]
```

### Attributes

Each shortcode takes a few attributes, indicating if it's required for public or private repositories:

| Attribute | Action                   | Public repo                   | Private repo                  | Type    | Description                                                                                                   |
|-----------|--------------------------|-------------------------------|-------------------------------|---------|---------------------------------------------------------------------------------------------------------------|
| `url`       | all except `git-add-css` | :ballot_box_with_check:       | :ballot_box_with_check:       | string  | The browser URL of the document, e.g. https://github.com/gis-ops/wordpress-markdown-git/blob/master/README.md |
| `user`      | all except `git-add-css` | :negative_squared_cross_mark: | :ballot_box_with_check:       | string  | The **user name** (not email) of an authorized user                                                           |
| `token`     | all except `git-add-css` | :negative_squared_cross_mark: | :ballot_box_with_check:       | string  | The access token/app password for the authorized user                                                         |
| `cache_ttl` | all except `git-add-css` | :negative_squared_cross_mark:       | :negative_squared_cross_mark:       | integer | The time in seconds that the plugin will cache, **only for `cache_strategy=static`**.           |
| `cache_strategy` | all except `git-add-css` | :negative_squared_cross_mark:       | :negative_squared_cross_mark:       | integer | Only `static` caching is implemented so far. `dynamic` caching is on the way!                   |
| `limit`     | `history`                | :negative_squared_cross_mark: | :negative_squared_cross_mark: | integer | Limits the history of commits to this number. Default 5.                                                      |
| `classes`   | `git-add-css`            | :ballot_box_with_check:       | :ballot_box_with_check:       | string  | The additional CSS classes to render the content with                                                         |

#### Global attributes

Since most attributes will be the same across the entire system, this plugin offers the possibility to set all attributes globally except for `url`:

In the menu *Plugins* ► *Plugin Editor*, choose "Documents from Git" and enter your preferences in the `includes/config.json`.

**Note**, setting the attributes manually in the shortcode has always precedence over any settings in `includes/config.json`.

#### Caching

Often we need to prioritize speed when loading content and, in addition, it is very costly to fetch, load and format the content every time we need to read the content of the post.

This plugin soon offers 2 methods for caching, `static` and `dynamic` which can be set via the `cache_strategy` property.

##### Static caching (`cache_strategy=static`)

This is the default strategy, as it doesn't require any user action.

The property `cache_ttl` sets how many **seconds** the content cache will keep alive.

Currently there's no way to flush the cache manually. However, changing `cache_ttl` or the history `limit` will create a new cache.

##### Dynamic caching (`cache_strategy=dynamic`)

**This is not implemented yet**. See [#4](https://github.com/gis-ops/wordpress-markdown-git/issues/4) for details.

#### `Token` authorization

You **need to** authorize via `user` and `token` if you intend to publish from a private repository. You **don't need to** authorize if the repository is open.

However, keep in mind that some platforms have stricter API limits for anonymous requests which are greatly extended if you provide your credentials. So even for public repos it could make sense. And it's strongly recommended to register a Github access token regardless of the VCS hosting platform, see the [beginning of the chapter](#usage).

How to generate the `token` depends on your platform:

- Github: Generate a Personal Access Token following [these instructions](https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line)
- Bitbucket: Generate a App Password following [these instructions](https://confluence.atlassian.com/bitbucket/app-passwords-828781300.html#Apppasswords-Createanapppassword)
- Gitlab: Generate a Personal Access Token following [these instructions](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html#creating-a-personal-access-token)

This plugin needs only **Read access** to your repositories. Keep that in mind when creating an access token.

### Examples

We publish our own tutorials with this plugin: https://gis-ops.com/tutorials/.

#### Publish Markdown from Github

`[git-github-markdown url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]`

#### Publish Markdown from Github with 1 hour cache

`[git-github-markdown url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md" cache_ttl="3600"]`

#### Publish Jupyter notebook from Github

`[git-github-jupyter url="https://github.com/GIScience/openrouteservice-examples/blob/master/python/ortools_pubcrawl.ipynb"]`

#### Publish from a private repository

`[git-bitbucket-jupyter user=nilsnolde token=3292_2p3a_84-2af url="https://bitbucket.org/nilsnolde/test-wp-plugin/src/master/README.md"]`

#### Display last commit and document URL from Bitbucket

`[git-bitbucket-checkout url="https://bitbucket.org/nilsnolde/test-wp-plugin/src/master/README.md"]`

#### Display commit history from Gitlab

`git-gitlab-history limit=5 url="https://gitlab.com/nilsnolde/esy-osm-pbf/-/blob/master/README.md"]`

#### Use additional CSS classes to style

The following example will put a dashed box around the whole post:

```
[git-add-css classes="md-dashedbox"]
    [git-github-checkout url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]
    [git-github-markdown url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]
    [git-github-history url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]
[/git-add-css]
```

With the following CSS file contents enqueued to your theme:

```css
div.md_dashedbox {
    position: relative;
    font-size: 0.75em;
    border: 3px dashed;
    padding: 10px;
    margin-bottom:15px
}

div.md_dashedbox div.markdown-github {
    color:white;
    line-height: 20px;
    padding: 0px 5px;
    position: absolute;
    background-color: #345;
    top: -3px;
    left: -3px;
    text-transform:none;
    font-size:1em;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
}
```

## Installation

### WordPress.org

The newest release of the plugin can be installed via WordPress plugin store under the name [Documents from Git](https://wordpress.org/plugins/documents-from-git/).

### Newest `master`

The newest version, which might not have made it into the plugin store yet, can be installed via the [WP Pusher](https://wppusher.com/download) plugin.

## Contributions from other projects

- [`github-markdown-css`](https://github.com/sindresorhus/github-markdown-css): CSS project for the Github flavored Markdown style, License MIT
    - This plugin maintains a copy of the CSS file
- [`nbconvert`](https://github.com/ghandic/nbconvert): Wordpress plugin to convert Jupyter notebooks into blog posts, License MIT
    - The idea for this plugin was mainly inspired by `nbconvert` and borrows some of the HTML and the CSS for Jupyter notebooks
- [Jason Long](https://twitter.com/jasonlong): Git logos under [Creative Commons Attribution 3.0 Unported License](https://creativecommons.org/licenses/by/3.0/)

## Troubleshooting

### Images

Images cannot (yet) be referenced with a relative link, i.e. `[some_image](./some_image.jpg)` won't work, as WordPress will try to access the image relative to your WordPress installation, e.g. `https://myblog.com/some_image.jpg`. See [#15](https://github.com/gis-ops/wordpress-markdown-git/issues/15) for a discussion on the topic.

The solution is to either
1. upload the image to WordPress and use the provided link
2. push the Markdown file to the cloud, view the file in the browser and copy the provider-generated image address
3. put the images in a repository folder and reference absolute links to your repository's raw files

**Note**, the last two options might be more involved when the image (alongside the Markdown file) is hosted in a private repository. Either the provider provides a token-authenticated URL for hosted images which you can use (see. e.g. [#13](https://github.com/gis-ops/wordpress-markdown-git/issues/13#issuecomment-638965192) for Bitbucket). Or publish the image(s) in a separate **public** repository. Or use option 1.

It's generally recommended to publish (and version) images alongside their Markdown document in Git.

### `API rate limit exceeded`

If you encounter this message instead of seeing your rendered Markdown file, you most likely have forgotten to provide your Github username and access token in the `config.json`. See [Global attributes](#global-attributes) for a How-To and [Usage](#usage) for more information why this is necessary.

## Sponsors

- [PDC](https://pdc.org): Sponsored the Bitbucket integration.

<a href="https://www.pdc.org" target="_blank"><img src="https://www.pdc.org/wp-content/uploads/2019/05/PDCLogo-Optimized.png" alt="alt text" height="40px"></a>
