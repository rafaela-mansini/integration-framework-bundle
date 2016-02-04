<?php

namespace Smartbox\Integration\FrameworkBundle\Connectors;


use Smartbox\Integration\FrameworkBundle\Exceptions\SoapConnectorException;
use Smartbox\Integration\FrameworkBundle\Traits\UsesSoapClient;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoapConfigurableConnector extends ConfigurableConnector {
    use UsesSoapClient;

    const REQUEST_PARAMETERS = 'parameters';
    const REQUEST_NAME = 'name';
    const SOAP_METHOD_NAME = 'soap_method';

    protected function request(array $stepActionParams, array $connectorOptions, array &$context)
    {
        $paramsResolver = new OptionsResolver();
        $paramsResolver->setRequired([
            self::SOAP_METHOD_NAME,
            self::REQUEST_PARAMETERS,
            self::REQUEST_NAME
        ]);

        $params = $paramsResolver->resolve($stepActionParams);

        $requestName = $params[self::REQUEST_NAME];
        $soapMethodName = $params[self::SOAP_METHOD_NAME];
        $soapMethodParams = $this->resolve($params[self::REQUEST_PARAMETERS], $context);

        $soapClient = $this->getSoapClient();
        if(!$soapClient){
            throw new \RuntimeException("SoapConfigurableConnector requires a SoapClient as a dependency");
        }

        try{
            $result = $soapClient->__soapCall($soapMethodName,$soapMethodParams);
        }catch (\Exception $ex){
            $exception = new SoapConnectorException($ex->getMessage());
            $exception->setRawRequest($soapClient->__getLastRequest());
            $exception->setRawResponse($soapClient->__getLastResponse());
            throw $exception;
        }

        $context[self::KEY_RESPONSES][$requestName] = $result;
    }
}