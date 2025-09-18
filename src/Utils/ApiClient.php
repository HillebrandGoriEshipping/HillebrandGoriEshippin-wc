<?php

namespace HGeS\Utils;

use HGeS\Exception\HttpException;
use HGeS\Utils\Enums\OptionEnum;
use Symfony\Component\HttpClient\HttpClient;

class ApiClient
{

    /**
     * Get the API URL from the configuration file
     * 
     * @return string The API URL
     */
    public static function getApiUrl(): string
    {
        $configRaw = file_get_contents(HGES_PLUGIN_CONFIG_PATH);
        $config = json_decode($configRaw, true);
        if (isset($config['apiUrl'])) {
            return $config['apiUrl'];
        }
    }

    /**
     * Make a GET request to the API
     * 
     * @param string $route The API route to call
     * @param bool $useToken Whether to use the token for authentication
     * @return array The response from the API
     * @throws \Exception If the request fails
     */
    public static function get(string $route, array $urlParams = [], array $headers = [], bool $useToken = true): array
    {
        $headers = array_merge(
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            $headers
        );

        if ($useToken) {
            $token = get_option(OptionEnum::HGES_ACCESS_KEY);
            if ($token) {
                $headers['X-AUTH-TOKEN'] = $token;
            }
        }

        $client = HttpClient::create();
        $url = self::getApiUrl() . $route;

        if (!empty($urlParams)) {
            $url .= '?' . urldecode(http_build_query($urlParams));
        }

        $response = $client->request(
            'GET',
            $url,
            [
                'headers' => $headers,
            ]
        );

        if ($response->getStatusCode() !== 200) {
            $content = $response->getContent(false);
            $message = '';
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
                $message = $data['message'];
            } else {
                $message = $content;
            }
            throw new HttpException('Error: ' . esc_html($message), $response->getStatusCode());
        }

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'data' => $response->toArray()
        ];
    }

    /**
     * Sends a POST request to the specified API route with optional headers and body.
     *
     * @param string $route The API route to send the request to.
     * @param array $body The request body to be sent as JSON. Defaults to an empty array.
     * @param array $headers Additional headers to include in the request. Defaults to an empty array.
     * @param bool $useToken Whether to include the authentication token in the headers. Defaults to true.
     * @return array An array containing the response status, headers, and data.
     * @throws \Exception If the response status code is not 200, throws an exception with the error message.
     */
    public static function post(string $route, array $body = [], array $headers = [], bool $useToken = true): array
    {
        $headers = array_merge(
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            $headers
        );

        if ($useToken) {
            $token = get_option(OptionEnum::HGES_ACCESS_KEY);
            if ($token) {
                $headers['X-AUTH-TOKEN'] = $token;
            }
        }

        $client = HttpClient::create();
        $url = self::getApiUrl() . $route;

        $response = $client->request(
            'POST',
            $url,
            [
                'headers' => $headers,
                'json' => $body
            ]
        );

        if ($response->getStatusCode() !== 200) {
            $content = $response->getContent(false);
            $message = '';
            $data = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
                $message = $data['message'];
            } else {
                $message = $content;
            }
            throw new \Exception('Error: ' . esc_html($message));
        }

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'data' => $response->toArray()
        ];
    }
}
