<?php

class GithubLoader extends BaseLoader {

    protected static $PROVIDER = 'Github';

    protected function extract_history_from_commit_json(array &$commit) {
        return array(
            $commit['commit']['author']['name'],
            $commit['commit']['author']['date'],
            $commit['commit']['message']
        );
    }

    protected function get_history() {
        list($response_body, $response_code) = $this->request_commits();
        return json_decode($response_body, true);
    }

    protected function get_checkout_datetime()
    {
        list($response_body, $response_code) = $this->request_commits();
        $json = json_decode($response_body, true);
        $datetime = $json[0]['commit']['committer']['date'];

        if ($response_body == "[]") {
            $response_code = 404;
        }

        return array($datetime, $response_code);
    }

    protected function get_document() {
        $args = array(
            'body' => array(
                'ref' => $this->branch
            ),
            'headers' => array(
                'Accept' => 'application/vnd.github.VERSION.raw'
            )
        );
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        }
        $get_url = "https://api.$this->domain/repos/$this->owner/$this->repo/contents/$this->file_path";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }

    protected function get_nbviewer_url()
    {
        $domain_exploded = explode('.', $this->domain);
        $domain_no_tld = $domain_exploded[count($domain_exploded) - 2];
        $url = "https://nbviewer.jupyter.org/$domain_no_tld/$this->owner/$this->repo/blob/$this->branch/$this->file_path?flush_cache=true";

        return $url;
    }

    /**
     * Helper function used to get commit history and last commit date
     */
    private function request_commits() {
        $args = array(
            'body' => array(
                'path' => $this->file_path,
                'sha' => $this->branch
            ),
            'headers' => array(
                'Accept' => 'application/json'
            )
        );
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        }
        $get_url = "https://api.$this->domain/repos/$this->owner/$this->repo/commits";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }
}