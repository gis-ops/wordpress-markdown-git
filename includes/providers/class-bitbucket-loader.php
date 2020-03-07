<?php

class BitbucketLoader extends BaseLoader {

    protected static $baseURL= 'https://api.bitbucket.org/2.0/';
    protected static $PROVIDER = 'Bitbucket';

    protected function extract_history_from_commit_json(&$commit) {
        return array(
            $commit['author']['raw'],
            $commit['date'],
            $commit['message']
        );
    }

    protected function get_history(&$owner, &$repo, &$branch, &$file_path) {
        list($response_body, $response_code) = $this->request_commits($owner, $repo, $branch, $file_path);
        return json_decode($response_body, true)['values'];
    }

    protected function get_checkout_datetime(&$owner, &$repo, &$branch, &$file_path)
    {
        list($response_body, $response_code) = $this->request_commits($owner, $repo, $branch, $file_path);
        $json = json_decode($response_body, true);
        $datetime = $json['values'][0]['date'];

        if ($json['values'] == "[]") {
            $response_code = 404;
        }

        return array($datetime, $response_code);
    }

    protected function get_markdown(&$owner, &$repo, &$branch, &$file_path) {
        $args = array(
            'headers' => array(
                'Authorization' => $this->get_auth_header()
            )
        );
        $get_url = self::$baseURL . "repositories/$owner/$repo/src/$branch/$file_path";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }

    private function request_commits(&$owner, &$repo, &$branch, &$file_path) {
        $args = array(
            'body' => array(
                'path' => $file_path
            ),
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => $this->get_auth_header()
            )
        );
        $get_url = self::$baseURL . "repositories/$owner/$repo/commits/$branch";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }
}