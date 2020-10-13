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
    public $apiTestUrl = 'https://stagetest.paysley.io/v2';

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Paysley\Paysley\Helper\Logger                $logger
     * @param \Paysley\Paysley\Helper\Curl                  $curl
     * @param \Magento\Framework\Locale\TranslatedLists   $translatedList
     */
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
        $url = $this->getApiUrl() . '/pos/generate-link';

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
        
        if (isset($response['message'])) {
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
}
