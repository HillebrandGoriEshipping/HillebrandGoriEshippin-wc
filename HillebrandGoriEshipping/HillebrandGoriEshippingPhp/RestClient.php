<?php

namespace HillebrandGoriEshipping\HillebrandGoriEshippingPhp;

/**
 * @author Hillebrand Gori eShipping
 * @copyright 2018 Hillebrand Gori eShipping
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Class RestClient
 * @package HillebrandGoriEshipping\HillebrandGoriEshippingPhp
 *
 *  Facilitates REST calls.
 */
class RestClient
{

    /**
     * Access key.
     *
     * @var string
     */
    private $accessKey;

    public static $GET = 'GET';
    public static $POST = 'POST';
    public static $PUT = 'PUT';
    public static $PATCH = 'PATCH';
    public static $DELETE = 'DELETE';

    /**
     * Construct function.
     *
     * @param string $accessKey access key.
     
     * @void
     */
    public function __construct($accessKey)
    {
        $this->accessKey = $accessKey;
    }

    /**
     * Healthcheck
     *
     * @return boolean
     */
    public static function healthcheck()
    {
        return self::fopenHealthcheck() || self::curlHealthcheck();
    }

    /**
     * fopen healthcheck
     *
     * @return boolean
     */
    private static function fopenHealthcheck()
    {
        $ini = ini_get('allow_url_fopen');
        return '' !== $ini && false !== $ini && '0' !== $ini && 0 !== $ini;
    }

    /**
     * curl healthcheck
     *
     * @return boolean
     */
    private static function curlHealthcheck()
    {
        return extension_loaded('curl');
    }

    /**
     * API request
     *
     * @param string $method one of GET, POST, PUT, PATCH, DELETE.
     * @param string $url url for the request.
     * @param array $params array of params.
     * @param array $headers array of headers.
     * @param int $timeout timeout in seconds.
     * @return ApiResponse
     */
    public function request($method, $url, $params = array(), $headers = array(), $body = array(), $timeout = null)
    {
        $headers['X-AUTH-TOKEN'] = $this->accessKey;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
            ),
        ));
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return  new ApiResponse($http_status, $response);
    }


    /**
     * Get adress status
     *
     * @return string
     */
    public function get_adress()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://test.eshipping.hillebrandgori.app/api/address/get-addresses?typeAddress=exp",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "X-AUTH-TOKEN:" . $key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return  $response;
    }

    public function get_pallet_size()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://test.eshipping.hillebrandgori.app/api/package/get-pallet-size",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "X-AUTH-TOKEN:" . $key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Get stream status
     *
     * @return string
     */
    private function getStreamStatus($stream)
    {
        $data = stream_get_meta_data($stream);
        $wrapperLines = $data['wrapper_data'];
        $matches = array();
        for ($i = count($wrapperLines); $i >= 1; $i--) {
            if (0 === strpos($wrapperLines[$i - 1], 'HTTP/1')) {
                preg_match('/(\d{3})/', $wrapperLines[$i - 1], $matches);
                break;
            }
        }
        return empty($matches) ? null : $matches[1];
    }

    /**
     * Check if fopen response content type is json
     *
     * @param array string response headers
     * @return boolean
     */
    private function isFopenResponseContentTypeJson($httpResponseHeaders)
    {
        $return = false;
        foreach ($httpResponseHeaders as $header) {
            if (-1 !== strpos('Content-Type: application/json', $header)) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Check if curl response content type is json
     *
     * @param curl request
     * @return boolean
     */
    private function isCurlResponseContentTypeJson($curl)
    {
        $curlInfo = curl_getinfo($curl);
        $contentType = explode(';', $curlInfo['content_type']);

        $return = false;
        foreach ($contentType as $type) {
            if (-1 !== strpos('application/json', $type)) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Get curl response status code
     *
     * @param curl request
     * @return boolean
     */
    private function getCurlResponseStatus($curl)
    {
        $curlInfo = curl_getinfo($curl);
        return $curlInfo['http_code'];
    }
}
