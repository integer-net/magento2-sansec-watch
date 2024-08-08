<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model\Exception;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

class CouldNotFetchPoliciesException extends LocalizedException
{
    public function __construct(
        Phrase $phrase,
        public readonly ?Throwable $previous = null
    ) {
        parent::__construct($phrase);
    }

    public static function fromInvalidSource(InvalidSource $invalidSource): self
    {
        return new self(__('Response is not valid JSON: %1', $invalidSource->getMessage()), $invalidSource);
    }

    public static function fromMappingError(MappingError $mappingError): self
    {
        return new self(__('Could not map response JSON to DTO: %1', $mappingError->getMessage()), $mappingError);
    }

    public static function fromTransportException(TransportExceptionInterface $transportException): self
    {
        return new self(__('Could not fetch policies from sansec watch: %1', $transportException), $transportException);
    }

    public static function fromHttpException(HttpExceptionInterface $httpException): self
    {
        return new self(__('Could not fetch policies from sansec watch: %1', $httpException), $httpException);
    }
}
