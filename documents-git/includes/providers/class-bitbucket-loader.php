<?php

class BitbucketLoader extends BaseLoader {

    protected static $PROVIDER = 'Bitbucket';

    protected function extract_history_from_commit_json(array &$commit) {
        return array(
            $commit['author']['raw'],
            $commit['date'],
            $commit['message']
        );
    }

    protected function get_history() {
        list($response_body, $response_code) = $this->request_commits();
        return json_decode($response_body, true)['values'];
    }

    protected function get_checkout_datetime()
    {
        list($response_body, $response_code) = $this->request_commits();
        $json = json_decode($response_body, true);
        $datetime = $json['values'][0]['date'];

        if ($json['values'] == "[]") {
            $response_code = 404;
        }

        return array($datetime, $response_code);
    }

    protected function get_document() {
        $args = array();
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        };
        $get_url = "https://api.$this->domain/2.0/repositories/$this->owner/$this->repo/src/$this->branch/$this->file_path";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }

    protected function get_nbviewer_url()
    {
        $url = "https://nbviewer.jupyter.org/urls/$this->domain/$this->owner/$this->repo/raw/$this->branch/$this->file_path";

        return $url;
    }

    /**
     * Helper function used to get commit history and last commit date
     */
    private function request_commits() {
        $args = array(
            'body' => array(
                'path' => $this->file_path
            ),
            'headers' => array(
                'Accept' => 'application/json'
            )
        );
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        }
        $get_url = "https://api.$this->domain/2.0/repositories/$this->owner/$this->repo/commits/$this->branch";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }
}