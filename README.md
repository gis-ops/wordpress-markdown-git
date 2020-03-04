# WordPress Plugin - Documents from Git

This WordPress Plugin lets you easily publish, collaborate on and version control your \[**Markdown**\] documents directly from your favorite remote Git platform.

The advantages are:

- Write Markdown in your favorite editor and just push to your remote repository to update your blog instantly
- Use the power of version control: publish different versions of the document in different posts, i.e. from another branch than `master`
- Easy to update by external users via pull requests, minimizes the chance of stale tutorials 

The following document types are currently supported:

- Markdown

The following platforms are currently supported:

- Github
- Bitbucket

## Usage

### Shortcodes

The plugin features a variety of shortcodes following a pattern of `[git-<platform>-<action>`, where

- `<platform>` can be one of
    - `github`: if you use Github as your VCS platform
    - `bitbucket`: if you use Bitbucket as your VCS platform
- `<action>` can be one of
    - `markdown`: Render your Markdown files hosted on your VCS platform in Github's render style
    - `checkout`: Renders a small badge-like box with a link to the document and the date of the last commit
    - `history`:  Renders a `<h2>` section with the last commit dates, messages and authors
    
### Attributes

Each shortcode takes a few attributes:

| Attribute | Action  | Required                      | Type    | Description                                                                                                   |
|-----------|---------|-------------------------------|---------|---------------------------------------------------------------------------------------------------------------|
| url       | all     | :ballot_box_with_check:       | string  | The browser URL of the document, e.g. https://github.com/gis-ops/wordpress-markdown-git/blob/master/README.md |
| user      | all     | :ballot_box_with_check:       | string  | The **user name** (not email) of an authorized user                                                           |
| token     | all     | :ballot_box_with_check:       | string  | The access token/app password for the authorized user*                                                        |
| limit     | history | :negative_squared_cross_mark: | integer | Limits the history of commits to this number.                                                                 |                                                               |

How to generate the `token` depends on your platform:

- Github: Generate a Personal Access Token following [these instructions](https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line)
- Bitbucket: Generate a App Password following [these instructions](https://confluence.atlassian.com/bitbucket/app-passwords-828781300.html#Apppasswords-Createanapppassword)

This plugin needs only **Read access** to your repositories. Keep that in mind when creating an access token.

### Examples

We publish our own tutorials with this plugin: https://gis-ops.com/tutorials/.

#### Publish Markdown from Github

`[git-github-markdown user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]`

#### Display last commit and document URL from Bitbucket

`[git-bitbucket-checkout user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://bitbucket.org/nilsnolde/test-wp-plugin/src/master/README.md"]`

#### Display commit history from Github

`git-github-history limit=5 user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]`

## Installation

### WordPress.org

The newest release of the plugin can be installed via WordPress plugin store.

### Newest `master`

The newest version, which might not have made it into the plugin store yet, can be installed via the [WP Pusher](https://wppusher.com/download) plugin.

## Contributions from other projects

- [`github-markdown-css`](https://github.com/sindresorhus/github-markdown-css): CSS project for the Github flavored Markdown style, License MIT
    - This plugin maintains a copy of the CSS file
- [`nbconvert`](https://github.com/ghandic/nbconvert): Wordpress plugin to convert Jupyter notebooks into blog posts, License MIT
    - The idea for this plugin was mainly inspired by `nbconvert` and borrows some of the HTML and CSS

## Sponsors

- [PDC](https://pdc.org): Sponsored the Bitbucket integration.

<a href="https://www.pdc.org" target="_blank"><img src="https://www.pdc.org/wp-content/uploads/2019/05/PDCLogo-Optimized.png" alt="alt text" height="40px"></a>
