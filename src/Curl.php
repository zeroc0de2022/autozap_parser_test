<?php
declare(strict_types = 1);
/***
 * Date 22.04.2023
 * @author zeroc0de <98693638+zeroc0de2022@users.noreply.github.com>
 */

namespace Parser;

/**
 * Curl
 * @noinspection PhpUnused
 */
class Curl
{

    /**
     * CURL settings received from the user
     * @var array
     */
    private array $settings;

    /**
     * CURL request options
     * @var array
     */
    private array $curlOptions;

    /**
     * Initialize CURL options by default
     * @return void
     */
    private function init(): void
    {
        $this->curlOptions = [
            CURLOPT_FAILONERROR    => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];
    }

    /**
     * For sending Curl request and getting result
     * @param array $settings []
     * @return array
     *  'status'  => (boolean)
     *  'headers' => (array)
     *  'body'    => (string)
     * @noinspection PhpUnused
     */
    public function request(array $settings): array
    {
        $this->init();
        $this->settings = $settings;
        foreach($this->settings as $keyopt => $value) {
            $this->curlOptions += $this->getCurlOption($keyopt, $value);
        }
        $curl_handle = curl_init();
        curl_setopt_array($curl_handle, $this->curlOptions);
        $response = $this->handleError($curl_handle);
        if(!is_array($response)) {
            $response = $this->curlResponse($response);
        }
        return $response;
    }

    /**
     * For sending and handling possible CURL request errors
     * @param $curl_handle - curl handler
     * @return string|array|bool
     */
    private function handleError($curl_handle): string|array|bool
    {
        $response = curl_exec($curl_handle);
        $errno    = curl_errno($curl_handle);

        // Empty, False response handler
        if($errno || empty($response)) {
            return ['status' => 0,
                    'body'   => curl_error($curl_handle) . '....'];
        }
        // Close connection
        curl_close($curl_handle);

        if(isset($settings['iconv'])) {
            extract($settings['iconv']);
            $response = iconv($from, $to, $response);
        }

        return $response;
    }

    /***
     * Prepare return array with final results of request execution
     * @param $response - response from server
     * @return array
     */
    private function curlResponse($response): array
    {
        $headers = '';
        if(isset($this->settings['get_headers']) && $this->settings['get_headers'] && str_contains("\r\n\r\n", $response)) {
            [$headers] = explode("\r\n\r\n", $response);
        }
        return ['status'  => true,
                'headers' => $headers,
                'body'    => trim(str_replace($headers, '', $response))];
    }

    /**
     * Returns the specified option for CURL
     * @param $key -key of option
     * @param $value -value of option
     * @return array
     */
    private function getCurlOption($key, $value): array
    {
        return match ($key) {
            'url'             => [CURLOPT_URL => $value],                          // request url
            'useragent'       => [CURLOPT_USERAGENT => $value],                    // useragent
            'new_session'     => [CURLOPT_COOKIESESSION => $value],                // new session
            'cookie_string'   => [CURLOPT_COOKIE => $value],                       // cookie
            'get_headers'     => [CURLOPT_HEADER => $value],                       // return headers
            'set_headers'     => [CURLOPT_HTTPHEADER => $value],                   // set headers
            'referer'         => [CURLOPT_REFERER => $value],                      // referer
            'nobody'          => [CURLOPT_NOBODY => $value],                       // return only headers
            'return'          => [CURLOPT_RETURNTRANSFER => $value],               // return result
            'follow_redirect' => [CURLOPT_FOLLOWLOCATION => $value],               // follow redirects
            'timeout'         => [CURLOPT_TIMEOUT => $value],                      // timeout in seconds
            'timeout_mc'      => [CURLOPT_TIMEOUT_MS => $value],                   // timeout in ms
            'connect_timeout' => [CURLOPT_CONNECTTIMEOUT => $value],               // connect timeout in s
            'proxy'           => $this->proxyForCurl($this->settings['proxy']),    // proxy
            'post'            => [CURLOPT_POSTFIELDS => $value,
                                  CURLOPT_POST       => true],                     // post data/request
            'cookie_file'     => [CURLOPT_COOKIEJAR  => $value,
                                  CURLOPT_COOKIEFILE => $value],                   // cookie file
            default           => []
        };
    }

    /**
     * Returns an array with proxy options for CURL
     * @param $proxy - proxy settings
     * @return array
     */
    private function proxyForCurl(array $proxy): array
    {
        $output = [];
        if(isset($proxy['ip'])) {
            $proxy_parts = explode(':', $proxy['ip']);
            $output      = [CURLOPT_PROXY     => $proxy_parts[0] . ':' . $proxy_parts[1],
                            CURLOPT_PROXYTYPE => constant('CURLPROXY_' . strtoupper($proxy['type'])) ?? CURLPROXY_HTTP];
            if(isset($proxyParts[2], $proxyParts[3])) {
                $output[CURLOPT_PROXYUSERPWD] = $proxyParts[2] . ':' . $proxyParts[3];
            }
        }
        return $output;
    }
}