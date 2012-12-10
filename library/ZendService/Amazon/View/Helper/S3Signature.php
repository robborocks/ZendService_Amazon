<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendService\Amazon\View\Helper;

use Zend\View\Helper\AbstractHelper as Helper;
use Zend\View\Model\ViewModel;

/**
 * A view helper for rendering an S3 policy signature
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 */
class S3Signature extends Helper
{
    /**
     * The policy configuration array for the view helper
     *
     * @var array
     */
    protected $config;

    /**
     * The S3 secret
     *
     * @var string
     */
    protected $secret;

    /**
     * Constructs the policy view helper
     *
     * @param array  $config
     * @param string $secret
     */
    public function __construct($config = array(), $secret = '')
    {
        $this->setConfig($config);
        $this->setSecret($secret);

        // hash_hmac — Generate a keyed hash value using the HMAC method
        // (PHP 5 >= 5.1.2, PECL hash >= 1.1)
        if ( !function_exists('hash_hmac' ) ) {
            // based on: http://www.php.net/manual/en/function.sha1.php#39492
            function hash_hmac($algo, $data, $key, $raw_output = false) {
                $blocksize = 64;
                if (strlen($key) > $blocksize)
                    $key = pack('H*', $algo($key));

                $key = str_pad($key, $blocksize, chr(0x00));
                $ipad = str_repeat(chr(0x36), $blocksize);
                $opad = str_repeat(chr(0x5c), $blocksize);
                $hmac = pack('H*', $algo(($key^$opad) . pack('H*', $algo(($key^$ipad) . $data))));

                return $raw_output ? $hmac : bin2hex($hmac);
            }
        }
    }

    /**
     * Outputs the signature for a policy configuration
     *
     * @param array  $config
     * @param string $secret
     *
     * @return string
     */
    public function __invoke($config = null, $secret = '')
    {
        if ( $config !== null ) {
            $this->setConfig($config);
        }

        if ( $secret != '' ) {
            $this->setSecret($secret);
        }

        $policy = base64_encode(
            json_encode(
                array(
                     // ISO 8601 - date('c'); generates uncompatible date, so better do it manually
                     'expiration' => date('Y-m-d\TH:i:s.000\Z', strtotime('+1 day')),
                     'conditions' => $this->getConfig()
                )
            )
        );

        // sign policy
        $signature =  base64_encode(hash_hmac('sha1', $policy, $this->getSecret(), true));

        return $signature;
    }

    /**
     * Sets the policy configuration
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Returns the policy configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets the S3 secret
     *
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Returns the S3 secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

}
