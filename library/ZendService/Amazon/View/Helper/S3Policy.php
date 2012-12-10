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
 * A view helper for rendering an S3 policy
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 */
class S3Policy extends Helper
{
    /**
     * The policy configuration array for the view helper
     *
     * @var array
     */
    protected $config;

    /**
     * Constructs the policy view helper
     *
     * @param array
     */
    public function __construct($config = array())
    {
        $this->setConfig($config);
    }

    /**
     * Outputs the encoded policy for a given policy configuration
     *
     * @return string
     */
    public function __invoke($config = null)
    {
        if ( $config !== null ) {
            $this->setConfig($config);
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

        return $policy;
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

}
