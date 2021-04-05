<?php

class GitlabLoader extends BaseLoader {

    protected static $PROVIDER = 'Gitlab';

    protected function extract_history_from_commit_json(array &$commit) {
        return array(
            $commit['author_name'],
            $commit['created_at'],
            $commit['message']
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
        $datetime = $json[0]['created_at'];

        if ($json['values'] == "[]") {
            $response_code = 404;
        }

        return array($datetime, $response_code);
    }

    protected function get_document() {
        $args = array(
            'body' => array(
                'ref' => $this->branch
            )
        );
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        }
        $get_url = "https://$this->domain/api/v4/projects/$this->owner/repository/files/$this->file_path/raw";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }

    protected function get_nbviewer_url()
    {
        $url = "https://nbviewer.jupyter.org/urls/$this->domain/$this->owner/$this->repo/-/raw/$this->branch/$this->file_path";

        return $url;
    }

    protected function set_repo_details(string $url)
    {
        $url_parsed = parse_url($url);
        $domain = $url_parsed['host'];
        $path = $url_parsed['path'];

        $exploded_path = explode('/-/', $path);
        $owner = ltrim($exploded_path[0], '/');

        $exploded_path_last = explode('/', $exploded_path[1]);
        $branch = $exploded_path_last[1];
        $file_path = urlencode(implode('/', array_slice($exploded_path_last, 2)));

        $this->domain = $domain;
        $this->owner = urlencode($owner);
        $this->branch = $branch;
        $this->file_path = $file_path;
    }

    protected function get_auth_header()
    {
        return "Bearer $this->token";
    }

    /**
     * Helper function used to get commit history and last commit date
     */
    private function request_commits() {
        $args = array(
            'body' => array(
                'path' => $this->file_path,
                'ref_name' => $this->branch
            ),
            'headers' => array(
                'Accept' => 'application/json'
            )
        );
        if (!empty($this->token)) {
            $args['headers']['Authorization'] = $this->get_auth_header();
        }
        $get_url = "https://$this->domain/api/v4/projects/$this->owner/repository/commits";

        $wp_remote = wp_remote_get($get_url, $args);
        $response_body = wp_remote_retrieve_body($wp_remote);
        $response_code = wp_remote_retrieve_response_code($wp_remote);

        return array($response_body, $response_code);
    }
}