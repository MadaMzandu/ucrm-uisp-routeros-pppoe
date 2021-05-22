<?php

class CS_Uisp {

    const API_URL = 'https://localhost/api/v1.0';
    const APP_KEY = '11ce2a09-6eb7-44f8-9a68-2d6cac30776a';

    private $ch;
    private $method;
    private $post;
    private $url;

    /**
     * @param string $url
     * @param string $method
     * @param array  $post
     *
     * @return array|null
     */
    public function http_request($url, $method = 'GET', $post = []) {
        $this->method = strtoupper($method);
        $this->ch = curl_init();
        $this->post = $post;
        $this->url = $url;
        $this->set_opts();
        return $this->exec();
    }

    private function exec() {
        $response = curl_exec($this->ch);
        if (curl_errno($this->ch) !== 0) {
            echo sprintf('Curl error: %s', curl_error($this->ch)) . PHP_EOL;
        }
        if (curl_getinfo($this->ch, CURLINFO_HTTP_CODE) >= 400) {
            echo sprintf('API error: %s', $response) . PHP_EOL;
            $response = false;
        }
        curl_close($this->ch);
        return $response !== false ? json_decode($response, true) : null;
    }

    private function set_opts() {
        $this->set_url();
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        $this->set_key();
        $this->set_method();
        $this->set_body();
    }

    private function set_key() {
        curl_setopt(
                $this->ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    sprintf('X-Auth-App-Key: %s', self::APP_KEY),
                ]
        );
    }

    private function set_url() {
        curl_setopt(
                $this->ch,
                CURLOPT_URL,
                sprintf(
                        '%s/%s',
                        self::API_URL,
                        $this->url
                )
        );
    }

    private function set_method() {
        if ($this->method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif ($this->method !== 'GET') {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method);
        }
    }

    private function set_body() {
        if (!empty($this->post)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->post));
        }
    }

}
