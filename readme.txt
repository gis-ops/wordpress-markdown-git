=== Documents from Git ===
Contributors: Nils Nolde
Tags: markdown,github,bitbucket
Requires at least: 5.0.0
Tested up to: 5.3.2
Requires PHP: 7.1
Stable tag: 1.0.0
License: GPLv3
License URI: https://github.com/gis-ops/wordpress-markdown-git/blob/master/LICENSE

A plugin to inject files directly into a post from popular Git platforms.

Currently supported file types: Markdown.

Currently supported platforms: Github, Bitbucket.

== Description ==


This WordPress Plugin lets you easily publish, collaborate on and version control your documents directly from your favorite remote Git platform.

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

Each shortcode can take a few attributes:

| attribute | action  | type    | description                                                                                                   |
|-----------|---------|---------|---------------------------------------------------------------------------------------------------------------|
| url       | all     | string  | The browser URL of the document, e.g. https://github.com/gis-ops/wordpress-markdown-git/blob/master/README.md |
| user      | all     | string  | The **user name** (not email) of an authorized user                                                           |
| token     | all     | string  | The access token/app password for the authorized user*                                                        |
| limit     | history | integer | Limits the history of commits to this number.                                                                 |

How to generate the `token` depends on your platform:

- Github: Generate a Personal Access Token following [these instructions](https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line)
- Bitbucket: Generate a App Password following [these instructions](https://confluence.atlassian.com/bitbucket/app-passwords-828781300.html#Apppasswords-Createanapppassword)

This plugin needs only **Read access** to your repositories. Keep that in mind when creating an access token.

### Examples

We publish our own tutorials with this plugin: https://gis-ops.com/tutorials/.

#### Publish Markdown from Github

`[git-github-markdown user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]`

#### Display last commit and Bitbucket document URL

`[git-bitbucket-checkout user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://bitbucket.org/nilsnolde/test-wp-plugin/src/master/README.md"]`

#### Display commit history

`git-github-history limit=5 user=nilsnolde token=hxsCL7LpnEp55FH9qK url="https://github.com/gis-ops/tutorials/blob/master/qgis/QGIS_SimplePlugin.md"]`

## Support

Add issues at <https://github.com/gis-ops/wordpress-markdown-git/issues>.

== Installation ==
1. Install WP Pusher (https://wppusher.com) via ZIP and activate
2. Install from Github via WP Pusher from gis-ops/wordpress-markdown-git
3. Activate and add shortcode to your posts.

Or directly from WordPress plugin repository.

Or install as ZIP from https://github.com/gis-ops/wordpress-markdown-git/archive/master.zip

== Changelog ==
= v1.0.0 =
* First version
