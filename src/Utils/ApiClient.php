<?php

namespace HGeS\Utils;

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
        $configRaw = file_get_contents(HGeS_PLUGIN_DIR . '/assets/js/conf.json');
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
    public static function get(string $route, array $urlParams = [], bool $useToken = true): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($useToken) {
            $token = get_option('HGES_ACCESS_KEY');
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
            throw new \Exception('Error: ' . $response->getStatusCode());
        }

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'data' => $response->toArray()
        ];
    }
}
