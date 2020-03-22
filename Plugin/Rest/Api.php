<?php

namespace JonathanMartz\WebApiCustomerLimit\Plugin\Rest;

use JonathanMartz\WebApiLog\Model\RequestFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlInterface;
use Magento\Webapi\Controller\Rest;
use Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Controller\Result\JsonFactory;
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
     * @var Session
     */
    private $customerSession;

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    protected $scopeConfig;

    const CONFIG_LIMIT = 'webapi-customer-limit/general/daily-limit';

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
        RequestFactory $webapilog,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->url = $url;
        $this->remote = $remote;
        $this->webapistats = $webapilog;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
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
        $url = str_replace([$this->url->getBaseUrl(), 'rest/V1/', 'index.php'], ['', '',''], $this->url->getCurrentUrl());
        $url = trim($url, '/');
        
        $ip = $this->remote->getRemoteAddress();

        $model = $this->webapistats->create();
        $collection = $model->getCollection();
        $collection->addFieldToFilter('ip' , ['eq' => sha1($ip)]);

        var_dump($this->scopeConfig->getValue(self::CONFIG_LIMIT, 'stores'));

        if(count($collection) > 100){
            $this->logger->alert('User blocked: ' . sha1($ip));
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData(['message' => 'Already created 100 users today.']);
            return $resultJson;
        }

        return $proceed($request);
    }
}
