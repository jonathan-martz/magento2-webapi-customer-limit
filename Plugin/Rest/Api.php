<?php

namespace JonathanMartz\WebApiCustomerLimit\Plugin\Rest;

use JonathanMartz\WebApiLog\Model\RequestFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlInterface;
use Magento\Webapi\Controller\Rest;
use Psr\Log\LoggerInterface;
use function json_encode;

/**
 * Class Api
 * @package JonathanMartz\WebApiCustomerLimit\Plugin\Rest
 */
class Api
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RequestFactory
     */
    protected $webapilog;

    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var RemoteAddress
     */
    private $remote;

    /**
     * Api constructor.
     * @param LoggerInterface $logger
     * @param UrlInterface $url
     * @param RemoteAddress $remote
     * @param RequestFactory $webapilog
     */
    public function __construct(
        LoggerInterface $logger,
        UrlInterface $url,
        RemoteAddress $remote,
        RequestFactory $webapilog
    ) {
        $this->logger = $logger;
        $this->url = $url;
        $this->remote = $remote;
        $this->webapistats = $webapilog;
    }

    /**
     * @param Rest $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        Rest $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        $url = str_replace([$this->url->getBaseUrl(), 'rest/V1/'], ['', ''], $this->url->getCurrentUrl());
        $ip = $this->remote->getRemoteAddress();

        $requestFactory = $this->requestFactory->create();

        $collection = $requestFactory->getCollection();
        $collection->addFieldToFilter('ip', array('eq' => sha1($ip)));

        var_dump(count($collection));
        
        return $proceed($request);
    }
}
