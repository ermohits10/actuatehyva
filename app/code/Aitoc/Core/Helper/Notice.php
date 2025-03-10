<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2022 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Core
 */


namespace Aitoc\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use SimpleXMLElement;
use Laminas\Http\Client\Adapter\Curl as CurlClient;
use Laminas\Http\Response as HttpResponse;
use Laminas\Uri\Http as HttpUri;
use Magento\Framework\Json\DecoderInterface;

class Notice extends AbstractHelper
{
    /**
     * @var CurlClient
     */
    protected $curlClient;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        DecoderInterface $jsonDecoder,
        CurlClient $curl,
        \Aitoc\Core\Helper\Config $config
    ) {
        parent::__construct($context);

        $this->cache = $cache;
        $this->curlClient = $curl;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->jsonDecoder = $jsonDecoder;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getNotificationTypes()
    {
        return $this->config->getNotificationsType();
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->config->getNotificationsEnable();
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->config->getNotificationsFrequency();
    }
}
