<?php

abstract class BaseLoader {
    protected static $GITHUB_MARKDOWN_API = 'https://api.github.com/markdown';
    protected static $PROVIDER;
    protected $user;
    protected $token;

    abstract protected function get_markdown(&$owner, &$repo, &$branch, &$file_path);
    abstract protected function get_checkout_datetime(&$owner, &$repo, &$branch, &$file_path);
    abstract protected function get_history(&$owner, &$repo, &$branch, &$file_path);
    abstract protected function extract_history_from_commit_json(&$commit);

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

    function doCheckout($sc_attrs)
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

    function doHistory($sc_attrs)
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

    function get_auth_header(){
        return 'Basic ' . base64_encode($this->user . ':' . $this->token);
    }

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