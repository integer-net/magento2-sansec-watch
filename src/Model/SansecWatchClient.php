<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use IntegerNet\SansecWatch\Mapper\PolicyMapper;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use IntegerNet\SansecWatch\Model\Event\FetchedPolicies;
use IntegerNet\SansecWatch\Model\Exception\CouldNotFetchPoliciesException;
use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SansecWatchClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PolicyMapper $policyMapper,
        private readonly ManagerInterface $eventManager,
        private readonly Config $config,
    ) {
    }

    /**
     * @return list<Policy>
     * @throws CouldNotFetchPoliciesException
     */
    public function fetchPolicies(Uuid $uuid): array
    {
        try {
            $responseJson = $this->fetchData($uuid);

            $policies = $this->policyMapper->map($responseJson);
        } catch (InvalidSource $invalidSource) {
            throw CouldNotFetchPoliciesException::fromInvalidSource($invalidSource);
        } catch (MappingError $mappingError) {
            throw CouldNotFetchPoliciesException::fromMappingError($mappingError);
        } catch (TransportExceptionInterface $transportException) {
            throw CouldNotFetchPoliciesException::fromTransportException($transportException);
        } catch (HttpExceptionInterface $httpException) {
            throw CouldNotFetchPoliciesException::fromHttpException($httpException);
        }

        $fetchedPolicies = new FetchedPolicies($policies);
        $this->eventManager->dispatch(
            'integernet_sansec_watch_fetched_policies',
            [
                'fetched_policies' => $fetchedPolicies,
            ]
        );

        return $fetchedPolicies->getPolicies();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws HttpExceptionInterface
     */
    private function fetchData(Uuid $uuid): string
    {
        $uri = str_replace(
            '{id}',
            $uuid->toRfc4122(),
            $this->config->getApiUrl()
        );

        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        $response = $this->httpClient->request('GET', $uri, $options);

        return $response->getContent();
    }
}
