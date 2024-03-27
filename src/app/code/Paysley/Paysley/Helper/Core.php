<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysley
 * @copyright   Copyright (c) 2020 Paysley
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Paysley\Paysley\Helper;

use \Magento\Framework\App\ProductMetadataInterface;

class Core extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * API Access Key
     *
     * @var string
     */
    public $accessKey;

    /**
     * Is use test server or not
     *
     * @var bool
     */
    public $isTestMode = false;

    /**
     * API live url
     *
     * @var string
     */
    public $apiLiveUrl = 'https://live.paysley.io/v2';

    /**
     * API test url
     *
     * @var string
     */
    public $apiTestUrl = 'https://test.paysley.io/v2';

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Paysley\Paysley\Helper\Logger                $logger
     * @param \Paysley\Paysley\Helper\Curl                  $curl
     * @param \Magento\Framework\Locale\TranslatedLists   $translatedList
     */

    protected $curl;
    protected $logger;
    protected $translatedList;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Paysley\Paysley\Helper\Logger $logger,
        \Paysley\Paysley\Helper\Curl $curl,
        \Magento\Framework\Locale\TranslatedLists $translatedList
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->curl = $curl;
        $this->translatedList = $translatedList;
    }

    /**
     * Get API url
     *
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->isTestMode) {
            return $this->apiTestUrl;
        }
        return $this->apiLiveUrl;
    }

    /**
     * get payment url postlink
     * @param  array $parameters
     * @return string
     */
    public function getPaymentUrl($parameters)
    {
        $url = $this->getApiUrl() . '/payment-requests';
        $this->logger->info('get api URL : '.$url);
        $this->logger->info(
            'get post link Parameters : '.
            json_encode($parameters)
        );
        return $this->curl->sendRequest($url, $parameters, $this->accessKey, 'POST');
    }

    /**
     * get CountryIso2 by iso3
     * @param  string $iso3
     * @return string
     */
    public function getCountryIso2($iso3)
    {
        $iso2 = [
             "AFG" => "AF",
             "ALB" => "AL",
             "DZA" => "DZ",
             "ASM" => "AS",
             "AND" => "AD",
             "AGO" => "AO",
             "AIA" => "AI",
             "ATA" => "AQ",
             "ATG" => "AG",
             "ARG" => "AR",
             "ARM" => "AM",
             "ABW" => "AW",
             "AUS" => "AU",
             "AUT" => "AT",
             "AZE" => "AZ",
             "BHS" => "BS",
             "BHR" => "BH",
             "BGD" => "BD",
             "BRB" => "BB",
             "BLR" => "BY",
             "BEL" => "BE",
             "BLZ" => "BZ",
             "BEN" => "BJ",
             "BMU" => "BM",
             "BTN" => "BT",
             "BOL" => "BO",
             "BIH" => "BA",
             "BWA" => "BW",
             "BVT" => "BV",
             "BRA" => "BR",
             "IOT" => "IO",
             "VGB" => "VG",
             "BRN" => "BN",
             "BGR" => "BG",
             "BFA" => "BF",
             "BDI" => "BI",
             "KHM" => "KH",
             "CMR" => "CM",
             "CAN" => "CA",
             "CPV" => "CV",
             "CYM" => "KY",
             "CAF" => "CF",
             "TCD" => "TD",
             "CHL" => "CL",
             "CHN" => "CN",
             "CXR" => "CX",
             "CCK" => "CC",
             "COL" => "CO",
             "COM" => "KM",
             "COG" => "CG",
             "COD" => "CD",
             "COK" => "CK",
             "CRI" => "CR",
             "HRV" => "HR",
             "CUB" => "CU",
             "CYP" => "CY",
             "CZE" => "CZ",
             "CIV" => "CI",
             "DNK" => "DK",
             "DJI" => "DJ",
             "DMA" => "DM",
             "DOM" => "DO",
             "ECU" => "EC",
             "EGY" => "EG",
             "SLV" => "SV",
             "GNQ" => "GQ",
             "ERI" => "ER",
             "EST" => "EE",
             "ETH" => "ET",
             "FLK" => "FK",
             "FRO" => "FO",
             "FJI" => "FJ",
             "FIN" => "FI",
             "FRA" => "FR",
             "GUF" => "GF",
             "PYF" => "PF",
             "ATF" => "TF",
             "GAB" => "GA",
             "GMB" => "GM",
             "GEO" => "GE",
             "DEU" => "DE",
             "GHA" => "GH",
             "GIB" => "GI",
             "GRC" => "GR",
             "GRL" => "GL",
             "GRD" => "GD",
             "GLD" => "GP",
             "GUM" => "GU",
             "GTM" => "GT",
             "GGY" => "GG",
             "HTI" => "GN",
             "HMD" => "GW",
             "VAT" => "GY",
             "GIN" => "HT",
             "GNB" => "HM",
             "HND" => "HN",
             "HKG" => "HK",
             "HUN" => "HU",
             "ISL" => "IS",
             "IND" => "IN",
             "IDN" => "ID",
             "IRN" => "IR",
             "IRQ" => "IQ",
             "IRL" => "IE",
             "IMN" => "IM",
             "ISR" => "IL",
             "ITA" => "IT",
             "JAM" => "JM",
             "JPN" => "JP",
             "JEY" => "JE",
             "JOR" => "JO",
             "KAZ" => "KZ",
             "KEN" => "KE",
             "KIR" => "KI",
             "KWT" => "KW",
             "KGZ" => "KG",
             "LAO" => "LA",
             "LVA" => "LV",
             "LBN" => "LB",
             "LSO" => "LS",
             "LBR" => "LR",
             "LBY" => "LY",
             "LIE" => "LI",
             "LTU" => "LT",
             "LUX" => "LU",
             "MAC" => "MO",
             "MKD" => "MK",
             "MDG" => "MG",
             "MWI" => "MW",
             "MYS" => "MY",
             "MDV" => "MV",
             "MLI" => "ML",
             "MLT" => "MT",
             "MHL" => "MH",
             "MTQ" => "MQ",
             "MRT" => "MR",
             "MUS" => "MU",
             "MYT" => "YT",
             "MEX" => "MX",
             "FSM" => "FM",
             "MDA" => "MD",
             "MCO" => "MC",
             "MNG" => "MN",
             "MNE" => "ME",
             "MSR" => "MS",
             "MAR" => "MA",
             "MOZ" => "MZ",
             "MMR" => "MM",
             "NAM" => "NA",
             "NRU" => "NR",
             "NPL" => "NP",
             "NLD" => "NL",
             "ANT" => "AN",
             "NCL" => "NC",
             "NZL" => "NZ",
             "NIC" => "NI",
             "NER" => "NE",
             "NGA" => "NG",
             "NIU" => "NU",
             "NFK" => "NF",
             "PRK" => "KP",
             "MNP" => "MP",
             "NOR" => "NO",
             "OMN" => "OM",
             "PAK" => "PK",
             "PLW" => "PW",
             "PSE" => "PS",
             "PAN" => "PA",
             "PNG" => "PG",
             "PRY" => "PY",
             "PER" => "PE",
             "PHL" => "PH",
             "PCN" => "PN",
             "POL" => "PL",
             "PRT" => "PT",
             "PRI" => "PR",
             "QAT" => "QA",
             "ROU" => "RO",
             "RUS" => "RU",
             "RWA" => "RW",
             "REU" => "RE",
             "BLM" => "BL",
             "SHN" => "SH",
             "KNA" => "KN",
             "LCA" => "LC",
             "MAF" => "MF",
             "SPM" => "PM",
             "WSM" => "WS",
             "SMR" => "SM",
             "SAU" => "SA",
             "SEN" => "SN",
             "SRB" => "RS",
             "SYC" => "SC",
             "SLE" => "SL",
             "SGP" => "SG",
             "SVK" => "SK",
             "SVN" => "SI",
             "SLB" => "SB",
             "SOM" => "SO",
             "ZAF" => "ZA",
             "SGS" => "GS",
             "KOR" => "KR",
             "ESP" => "ES",
             "LKA" => "LK",
             "VCT" => "VC",
             "SDN" => "SD",
             "SUR" => "SR",
             "SJM" => "SJ",
             "SWZ" => "SZ",
             "SWE" => "SE",
             "CHE" => "CH",
             "SYR" => "SY",
             "STP" => "ST",
             "TWN" => "TW",
             "TJK" => "TJ",
             "TZA" => "TZ",
             "THA" => "TH",
             "TLS" => "TL",
             "TGO" => "TG",
             "TKL" => "TK",
             "TON" => "TO",
             "TTO" => "TT",
             "TUN" => "TN",
             "TUR" => "TR",
             "TKM" => "TM",
             "TCA" => "TC",
             "TUV" => "TV",
             "UMI" => "UM",
             "VIR" => "VI",
             "UGA" => "UG",
             "UKR" => "UA",
             "ARE" => "AE",
             "GBR" => "GB",
             "USA" => "US",
             "URY" => "UY",
             "UZB" => "UZ",
             "VUT" => "VU",
             "GUY" => "VA",
             "VEN" => "VE",
             "VNM" => "VN",
             "WLF" => "WF",
             "ESH" => "EH",
             "YEM" => "YE",
             "ZMB" => "ZM",
             "ZWE" => "ZW",
             "ALA" => "AX"        ];
        if (array_key_exists($iso3, $iso2)) {
            return $iso2[$iso3];
        }
        return '';
    }

    /**
     * get country name from country iso code
     * @param  string $country
     * @return string
     */
    public function getCountryName($country)
    {
        if (strlen($country) == 3) {
            $country = $this->getCountryIso2($country);
        }
        if (!empty($country)) {
            return $this->translatedList->getCountryTranslation($country);
        }
        return '';
    }

    /**
     * get comment order history
     * @param  array $response
     * @return string
     */
    public function getComment($response)
    {
        $separator = ". ";
        $comment = "";
        if (!empty($response['message'])) {
            $comment .= __('Message')." : ".$response['message'].$separator;
        }
        return $comment;
    }

    /**
     * do refund
     * @param  array $parameters
     * @return boolean | xml
     */
    public function doRefund($paymentId, $parameters)
    {
        $url = $this->getApiUrl() . '/refunds/' . $paymentId;
        
        $this->logger->info('refund URL : '.$url);
        $this->logger->info(
            'doRefund prepare parameters : '.
            json_encode($parameters)
        );
        
        return $this->curl->sendRequest($url, $parameters, $this->accessKey, 'POST');
    }

    /**
     * get the version of magento
     *
     * @return string
     */
    public function getShopVersion()
    {
        if (defined('\Magento\Framework\AppInterface::VERSION')) {
            return \Magento\Framework\AppInterface::VERSION;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetaData = $objectManager->create(ProductMetadataInterface::class);
        return $productMetaData->getVersion();
    }

    /**
	 * Get lists of products.
     * @param string $productName
     * @return array product response
	 */
	public function getProducts($productName = null)
	{
		$url = $this->getApiUrl() . '/products-services';
		if (!empty($productName)) {
			$url .= "?keywords=".urlencode($productName);
		}
        $this->logger->info('get products URL : '.$url);
        return $this->curl->sendRequest($url, "", $this->accessKey);
	}

    /**
	 * Function to create the product on the paysley
	 * Create new Product.
     * @param $body  Body of create product api
     * @return array api response
	 */
	public function createProduct($body)
	{
		$url = $this->getApiUrl() . '/products-services';
        $this->logger->info('Create product URL : '.$url);
        $this->logger->info(
            'Create product parameters : '.
            json_encode($body)
        );
        return $this->curl->sendRequest($url, $body, $this->accessKey, 'POST');
	}

    /**
	 * Function to update the product on the paysley
     * @param $body  Body of update product api
     * @return array api response
	 */
	public function updateProduct($body)
	{
		$url = $this->getApiUrl() . '/products-services';
        $this->logger->info('Update product URL : '.$url);
        $this->logger->info(
            'Updatae product parameters : '.
            json_encode($body)
        );
        return $this->curl->sendRequest($url, $body, $this->accessKey, 'PUT');
	}

    /**
	 * Get list of categories.
	 * @param string $categoryName
	 * @return category list api response
	 */
	public function categoryList($categoryName = null)
	{
		$url = $this->getApiUrl() . '/products-services/category';
		if (!empty($categoryName)) {
			$url .= "?keywords=".urlencode($categoryName);
		}
        $this->logger->info('Category list api URL : '.$url);
		return $this->curl->sendRequest($url, "", $this->accessKey);
	}

    /**
	 * Function to creaate catogry on paysley.
	 * @param array $body
	 * @return create category api response
	 */
	public function createCategory($body)
	{
		$url = $this->getApiUrl() . '/products-services/category';
        $this->logger->info('Create category api URL : '.$url);
        $this->logger->info(
            'Create category api parameters : '.
            json_encode($body)
        );
        return $this->curl->sendRequest($url, $body, $this->accessKey, 'POST');
	}

    /**
	 * Fucntion to get the customer List.
     * @param string $searchKeyword
     * @return customerlist api response
	 */
	public function customerList($searchKeyword = null)
	{
		$url = $this->getApiUrl() . '/customers';
		if ($searchKeyword){
			$url .= "?keywords=".urlencode($searchKeyword)."&limit=500";
        }
        $this->logger->info('Customer list api URL : '.$url);
        return $this->curl->sendRequest($url, "", $this->accessKey);
	}


    /**
	 * Function to create new customer.
     * @param array $body
     * @return createcustomer api response  
	 */
	public function createCustomer($body)
	{
		$url = $this->getApiUrl() . '/customers';
        $this->logger->info('Create customer api  URL : '.$url);
        $this->logger->info(
            'Create customer api parameters : '.
            json_encode($body)
        );
        $this->curl->sendRequest($url, $body, $this->accessKey, 'POST');
	}

    /**
	 * Function to update customer.
     * @param array $body
     *  @return array updatecustomer api response
	 */
	public function updateCustomer($body)
	{
		$url = $this->getApiUrl() . '/customers';
        $this->logger->info('Update customer api  URL : '.$url);
        $this->logger->info(
            'Update customer api parameters : '.
            json_encode($body)
        );
        return $this->curl->sendRequest($url, $body, $this->accessKey, 'PUT');
	}

    /**
	 * Get payment details
     * @param int $transactionId
     * @return array paymentdetail api response 
	 */
	public function getPaymentDetails($transactionId)
	{
		$url = $this->getApiUrl() . '/payment-requests/'.$transactionId;
        return $this->curl->sendRequest($url, "", $this->accessKey);
	}


    /**
     * get getCountryPhoneCode by country_iso
     * @param  string $country_iso
     * @return string
     */
    public function getCountryPhoneCode($country_iso)
    {
        $countryPhoneCodes = [
            'AF' => '+93',
			'AL' => '+355',
			'DZ' => '+213',
			'AS' => '+1-684',
			'AD' => '+376',
			'AO' => '+244',
			'AI' => '+1-264',
			'AQ' => '+672',
			'AG' => '+1-268',
			'AR' => '+54',
			'AM' => '+374',
			'AW' => '+297',
			'AU' => '+61',
			'AT' => '+43',
			'AZ' => '+994',
			'BS' => '+1-242',
			'BH' => '+973',
			'BD' => '+880',
			'BB' => '+1-246',
			'BY' => '+375',
			'BE' => '+32',
			'BZ' => '+501',
			'BJ' => '+229',
			'BM' => '+1-441',
			'BT' => '+975',
			'BO' => '+591',
			'BA' => '+387',
			'BW' => '+267',
			'BR' => '+55',
			'IO' => '+246',
			'VG' => '+1-284',
			'BN' => '+673',
			'BG' => '+359',
			'BF' => '+226',
			'BI' => '+257',
			'KH' => '+855',
			'CM' => '+237',
			'CA' => '+1',
			'CV' => '+238',
			'KY' => '+1-345',
			'CF' => '+236',
			'TD' => '+235',
			'CL' => '+56',
			'CN' => '+86',
			'CX' => '+61',
			'CC' => '+61',
			'CO' => '+57',
			'KM' => '+269',
			'CK' => '+682',
			'CR' => '+506',
			'HR' => '+385',
			'CU' => '+53',
			'CW' => '+599',
			'CY' => '+357',
			'CZ' => '+420',
			'CD' => '+243',
			'DK' => '+45',
			'DJ' => '+253',
			'DM' => '+1-767',
			'DO' => '+1-809',
			'TL' => '+670',
			'EC' => '+593',
			'EG' => '+20',
			'SV' => '+503',
			'GQ' => '+240',
			'ER' => '+291',
			'EE' => '+372',
			'ET' => '+251',
			'FK' => '+500',
			'FO' => '+298',
			'FJ' => '+679',
			'FI' => '+358',
			'FR' => '+33',
			'PF' => '+689',
			'GA' => '+241',
			'GM' => '+220',
			'GE' => '+995',
			'DE' => '+49',
			'GH' => '+233',
			'GI' => '+350',
			'GR' => '+30',
			'GL' => '+299',
			'GD' => '+1-473',
			'GU' => '+1-671',
			'GT' => '+502',
			'GG' => '+44-1481',
			'GN' => '+224',
			'GW' => '+245',
			'GY' => '+592',
			'HT' => '+509',
			'HN' => '+504',
			'HK' => '+852',
			'HU' => '+36',
			'IS' => '+354',
			'IN' => '+91',
			'ID' => '+62',
			'IR' => '+98',
			'IQ' => '+964',
			'IE' => '+353',
			'IM' => '+44-1624',
			'IL' => '+972',
			'IT' => '+39',
			'CI' => '+225',
			'JM' => '+1-876',
			'JP' => '+81',
			'JE' => '+44-1534',
			'JO' => '+962',
			'KZ' => '+7',
			'KE' => '+254',
			'KI' => '+686',
			'XK' => '+383',
			'KW' => '+965',
			'KG' => '+996',
			'LA' => '+856',
			'LV' => '+371',
			'LB' => '+961',
			'LS' => '+266',
			'LR' => '+231',
			'LY' => '+218',
			'LI' => '+423',
			'LT' => '+370',
			'LU' => '+352',
			'MO' => '+853',
			'MK' => '+389',
			'MG' => '+261',
			'MW' => '+265',
			'MY' => '+60',
			'MV' => '+960',
			'ML' => '+223',
			'MT' => '+356',
			'MH' => '+692',
			'MR' => '+222',
			'MU' => '+230',
			'YT' => '+262',
			'MX' => '+52',
			'FM' => '+691',
			'MD' => '+373',
			'MC' => '+377',
			'MN' => '+976',
			'ME' => '+382',
			'MS' => '+1-664',
			'MA' => '+212',
			'MZ' => '+258',
			'MM' => '+95',
			'NA' => '+264',
			'NR' => '+674',
			'NP' => '+977',
			'NL' => '+31',
			'AN' => '+599',
			'NC' => '+687',
			'NZ' => '+64',
			'NI' => '+505',
			'NE' => '+227',
			'NG' => '+234',
			'NU' => '+683',
			'KP' => '+850',
			'MP' => '+1-670',
			'NO' => '+47',
			'OM' => '+968',
			'PK' => '+92',
			'PW' => '+680',
			'PS' => '+970',
			'PA' => '+507',
			'PG' => '+675',
			'PY' => '+595',
			'PE' => '+51',
			'PH' => '+63',
			'PN' => '+64',
			'PL' => '+48',
			'PT' => '+351',
			'PR' => '+1-787',
			'QA' => '+974',
			'CG' => '+242',
			'RE' => '+262',
			'RO' => '+40',
			'RU' => '+7',
			'RW' => '+250',
			'BL' => '+590',
			'SH' => '+290',
			'KN' => '+1-869',
			'LC' => '+1-758',
			'MF' => '+590',
			'PM' => '+508',
			'VC' => '+1-784',
			'WS' => '+685',
			'SM' => '+378',
			'ST' => '+239',
			'SA' => '+966',
			'SN' => '+221',
			'RS' => '+381',
			'SC' => '+248',
			'SL' => '+232',
			'SG' => '+65',
			'SX' => '+1-721',
			'SK' => '+421',
			'SI' => '+386',
			'SB' => '+677',
			'SO' => '+252',
			'ZA' => '+27',
			'KR' => '+82',
			'SS' => '+211',
			'ES' => '+34',
			'LK' => '+94',
			'SD' => '+249',
			'SR' => '+597',
			'SJ' => '+47',
			'SZ' => '+268',
			'SE' => '+46',
			'CH' => '+41',
			'SY' => '+963',
			'TW' => '+886',
			'TJ' => '+992',
			'TZ' => '+255',
			'TH' => '+66',
			'TG' => '+228',
			'TK' => '+690',
			'TO' => '+676',
			'TT' => '+1-868',
			'TN' => '+216',
			'TR' => '+90',
			'TM' => '+993',
			'TC' => '+1-649',
			'TV' => '+688',
			'VI' => '+1-340',
			'UG' => '+256',
			'UA' => '+380',
			'AE' => '+971',
			'GB' => '+44',
			'US' => '+1',
			'UY' => '+598',
			'UZ' => '+998',
			'VU' => '+678',
			'VA' => '+379',
			'VE' => '+58',
			'VN' => '+84',
			'WF' => '+681',
			'EH' => '+212',
			'YE' => '+967',
			'ZM' => '+260',
			'ZW' => '+263',
        ];
        if (array_key_exists($country_iso, $countryPhoneCodes)) {
            return $countryPhoneCodes[$country_iso];
        }
        return '';
    }
}
