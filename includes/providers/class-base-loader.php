<?php

abstract class BaseLoader {
    protected static $GITHUB_MARKDOWN_API = 'https://api.github.com/markdown';
    protected static $PROVIDER;
    protected $user;
    protected $token;

    public function __construct() {
        if (!isset(static::$PROVIDER)) {
            throw new LogicException("Class property PROVIDER must be set for " . static::class);
        }
        $provider = strtolower(static::$PROVIDER);
        add_shortcode("git-$provider-markdown", array($this, 'doPost'));
        add_shortcode("git-$provider-checkout", array($this, 'doCheckout'));
        add_shortcode("git-$provider-history", array($this, 'doHistory'));
    }

    /**
     * The API specific function to return the raw Markdown document and HTTP response code.
     * In case of an API error, the response body is not used.
     *
     * @param $owner string the repository's owner
     * @param $repo string the repository's name
     * @param $branch string the desired branch or commit SHA
     * @param $file_path string the relative file path within the repository
     * @return mixed array of response body and HTTP response code
     */
    abstract protected function get_markdown(&$owner, &$repo, &$branch, &$file_path);

    /**
     * The API specific function to return the raw date of the last commit of the file and HTTP response code.
     * In case of an API error, the date string variable is not used.
     *
     * @param $owner string the repository's owner
     * @param $repo string the repository's name
     * @param $branch string the desired branch or commit SHA
     * @param $file_path string the relative file path within the repository
     * @return mixed array of raw date string and response HTTP code
     */
    abstract protected function get_checkout_datetime(&$owner, &$repo, &$branch, &$file_path);

    /**
     * The API specific function to return the response JSON array for all commits of the file and HTTP response code.
     * In case of an API error, the response JSON array is not used.
     *
     * @param $owner string the repository's owner
     * @param $repo string the repository's name
     * @param $branch string the desired branch or commit SHA
     * @param $file_path string the relative file path within the repository
     * @return mixed array of associative array of the response JSON and response HTTP code
     */
    abstract protected function get_history(&$owner, &$repo, &$branch, &$file_path);

    /**
     * The API specific function to parse the name, date and message per commit.
     *
     * @param $commit array The associative array for a single commit from the JSON response
     * @return mixed array of name, date and message
     */
    abstract protected function extract_history_from_commit_json(&$commit);

    /**
     * The callback function for the "post" shortcode action.
     *
     * @param $sc_attrs array Shortcode attributes
     * @return string HTML of the whole Markdown document processed by Github's markdown endpoint
     */
    public function doPost($sc_attrs)
    {
        # Normalize shortcode attributes to lower case
        $sc_attrs = array_change_key_case((array)$sc_attrs, CASE_LOWER);

        list($url, $limit) = $this->extract_attributes($sc_attrs);
        list($owner, $repo, $branch, $file_path) = $this->extract_url($url);
        list($raw_markdown, $response_code) = $this->get_markdown($owner, $repo, $branch, $file_path);

        switch ($response_code) {
            case 200:
                break;
            case 404:
                $raw_markdown = "# 404 - Not found\nPost not found on $url";
                break;
            case 401:
                $raw_markdown = "# 401 - Bad credentials.\nPlease review access token for user " . $this->user;
                break;
            default:
                $raw_markdown = "# 500 - Server Error.\n$raw_markdown";
        }

        $args = array(
            'body' => json_encode(array(
                "text" => $raw_markdown
            )),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        );
        $response = wp_remote_post(self::$GITHUB_MARKDOWN_API, $args);
        $html_body = wp_remote_retrieve_body($response);

        return '<div>' . $html_body . '</div>';
    }

    /**
     * The callback function for the "checkout" shortcode action.
     *
     * @param $sc_attrs array Shortcode attributes
     * @return string HTML for the checkout span
     */
    public function doCheckout($sc_attrs)
    {
        # Normalize shortcode attributes to lower case
        $sc_attrs = array_change_key_case((array)$sc_attrs, CASE_LOWER);

        list($url, $limit) = $this->extract_attributes($sc_attrs);
        list($owner, $repo, $branch, $file_path) = $this->extract_url($url);

        list($datetime_str, $response_code) = $this->get_checkout_datetime($owner, $repo, $branch, $file_path);

        switch ($response_code) {
            case 200:
                $datetime = strtotime($datetime_str);
                $html_body = date('d/m/Y H:i:s', $datetime);
                break;
            case 401:
                $html_body = "401 - Invalid credentials for user " . $this->user;
                break;
            case 404:
                $html_body = "404 - Post not found on $url";
        }

        return '
        <div class="markdown-github">
          <div class="markdown-github-labels">
            <label class="github-link">
              <a href="' . $url . '" target="_blank">Check it out on ' . static::$PROVIDER . '</a>
              <label class="github-last-update"> Last updated: ' . $html_body . '</label>
            </label>
          </div>
        </div>';
    }

    /**
     * The callback function for the "history" shortcode action.
     *
     * @param $sc_attrs array Shortcode attributes
     * @return string HTML for the Last X commits section
     */
    public function doHistory($sc_attrs)
    {
        # Normalize shortcode attributes to lower case
        $sc_attrs = array_change_key_case((array)$sc_attrs, CASE_LOWER);

        list($url, $limit) = $this->extract_attributes($sc_attrs);
        list($owner, $repo, $branch, $file_path) = $this->extract_url($url);

        $html_string = '<hr style="margin: 20px 0; width: 70%; border-top: 1.5px solid #aaaaaa;" /><article class="markdown-body"><h2><strong><a target="_blank" href="' . $url . '">Post history - Last 5 commits</a></strong></h2>';

        $commits_json = $this->get_history($owner, $repo,$branch,$file_path);

        $i = 0;
        foreach ($commits_json as $item) {
            if ($i == intval($limit)) break;
            list($name, $date, $message) =  $this->extract_history_from_commit_json($item);
            $html_string .= "<p>";
            $html_string .= "<strong>" . date('d/m/Y H:i:s', strtotime($date)) . "</strong> - $message";
            $html_string .= " ( $name )";
            $html_string .= '</p>';
            $i++;
        }
        $html_string .= '</article>';

        return $html_string;
    }

    /**
     * Determines and returns the correct Authorization header, depending on the API
     *
     * @return string Base64 encoded Basic Authorization header
     */
    protected function get_auth_header(){
        return 'Basic ' . base64_encode($this->user . ':' . $this->token);
    }

    /**
     * Extract the relevant attributes from the URL provided by the "url" shortcode attribute.
     *
     * @param $url string URL of the file to be rendered
     * @return array array of relevant URL attributes
     */
    private function extract_url($url)
    {
        $url_exploded = explode('/', parse_url($url, PHP_URL_PATH));

        return array(
            $url_exploded[1],  # owner
            $url_exploded[2],  # repo
            $url_exploded[4],  # branch
            implode('/', array_slice($url_exploded, 5))  # file path
        );
    }

    /**
     * Extracts the attributes from a shortcode. All attributes of all shortcodes are extracted,
     * but not necessarily passed, so they default to an empty string.
     *
     * It also sets the class attributes "user" and "token".
     *
     * @param $attrs array Attributes of the shortcode
     * @return array parsed url and limit
     */
    private function extract_attributes($attrs) {
        extract(shortcode_atts(array(
                'url' => "",
                'user' => "",
                'token' => "",
                'limit' => ""
            ), $attrs
            )
        );

        $this->user = $user;
        $this->token = $token;

        return array($url, $limit);
    }
}