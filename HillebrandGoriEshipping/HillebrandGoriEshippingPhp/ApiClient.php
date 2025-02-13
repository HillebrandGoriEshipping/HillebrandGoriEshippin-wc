<?php

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

namespace HillebrandGoriEshipping\HillebrandGoriEshippingPhp;

/**
 * Class ApiClient
 * @package HillebrandGoriEshipping\HillebrandGoriEshippingPhp
 *
 *  Facilitates Hillebrand Gori eShipping API calls.
 */
class ApiClient
{

    /**
     * Public key.
     *
     * @var RestClient
     */
    public $restClient;

    /**
     * Construct function.
     *
     * @param string $accessKey access key.
    
     * @void
     */
    public function __construct($accessKey)
    {
        $this->restClient = new RestClient($accessKey);
    }

    /**
     * Get api url.
     *
     * @return string
     */
    public function getApiUrl()
    {
        $content = file_get_contents(__DIR__ . '/config.json');
        $config = json_decode($content);
        return $config->apiUrl;
    }

    /**
     * Get parcel points around a given address for UPS.
     *
     * @param array address fields
     * ex : array('street' => '4 boulevard...', 'postcode' => '75009', 'city' => 'Paris', 'country' => 'FR'))
     * @param array parcel point networks (ex: ['MONR_NETWORK', 'SOGP_NETWORK'])
     * @return ApiResponse
     */
    public function getParcelPoints($address, $networks = array())
    {
        $params = array(
            'networks' => $networks,
            'zipCode' => $address['zipCode'],
            'country' => $address['country']
        );
        if (isset($address['street'])) {
            $params['street'] = $address['street'];
        }

        if (isset($address['city'])) {
            $params['city'] = $address['city'];
        }

        return $this->restClient->request(RestClient::$GET, $this->getApiUrl() . 'relay/get-access-points', $params);
    }

    /**
     * Get parcel points around a given address for Chronopost.
     *
     * @param array address fields
     * ex : array('street' => '4 boulevard...', 'postcode' => '75009', 'city' => 'Paris', 'country' => 'FR'))
     * @param array parcel point networks (ex: ['MONR_NETWORK', 'SOGP_NETWORK'])
     * @return ApiResponse
     */
    public function getChronopostParcelPoints($address, $networks = array())
    {
        $params = array(
            'networks' => $networks,
            'zipCode' => $address['zipCode'],
            'productCode' => '86',
            'shipmentDate' => $address['shipmentDate'],
            'country' => $address['country'],
        );
        if (isset($address['street'])) {
            $params['street'] = $address['street'];
        }

        if (isset($address['city'])) {
            $params['city'] = $address['city'];
        }

        return $this->restClient->request(RestClient::$GET, $this->getApiUrl() . 'relay/get-chronopost-relay-points', $params);
    }

    /**
     * Get order.
     *
     * @param String order reference
     * @return ApiResponse
     */
    public function getOrder($reference)
    {
        return $this->restClient->request(RestClient::$GET, $this->getApiUrl() . 'expedition/get-expeditions');
    }
}
