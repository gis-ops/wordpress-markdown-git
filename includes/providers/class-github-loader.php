<?php

include_once('class-base-loader.php');

class GithubLoader extends BaseLoader {

    protected static $baseURL= 'https://api.github.com/';
    protected static $PROVIDER = 'Github';

    public function __construct() {
        add_shortcode('git-github-markdown', array($this, 'doPost'));
        add_shortcode('git-github-checkout', array($this, 'doCheckout'));
        add_shortcode('git-github-history', array($this, 'doHistory'));
    }

    protected function extract_history_from_commit_json(&$commit) {
        return array(
            $commit['commit']['author']['name'],
            $commit['commit']['author']['date'],
            $commit['commit']['message']
        );
    }

    protected function get_history(&$owner, &$repo, &$branch, &$file_path) {
        list($response_body, $response_code) = $this->request_commits($owner, $repo, $branch, $file_path);
        return json_decode($response_body, true);
    }

    protected function get_checkout_datetime(&$owner, &$repo, &$branch, &$file_path)
    {
        list($response_body, $response_code) = $this->request_commits($owner, $repo, $branch, $file_path);
        $json = json_decode($response_body, true);
        $datetime = $json[0]['commit']['committer']['date'];

        if ($response_body == "[]") {
            $response_code = 404;
        }

        return array($datetime, $response_code);
    }

    protected function get_markdown(&$owner, &$repo, &$branch, &$file_path) {
        $args = array(
            'body' => array(
                'ref' => $branch
            ),
            'headers' => array(
                'Accept' => 'application/vnd.github.VERSION.raw',
                'Authorization' => $this->get_auth_header()
            )
        );
        $get_url = self::$baseURL . 'repos/' . $owner . '/' . $repo . '/contents/' . $file_path;

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }

    private function request_commits(&$owner, &$repo, &$branch, &$file_path) {
        $args = array(
            'body' => array(
                'path' => $file_path,
                'sha' => $branch
            ),
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => $this->get_auth_header()
            )
        );
        $get_url = self::$baseURL . 'repos/' . $owner . '/' . $repo . '/commits';

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }
}