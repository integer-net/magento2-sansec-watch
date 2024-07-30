<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use IntegerNet\SansecWatch\Model\DTO\Policy;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function sprintf;

class SansecWatchClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return list<Policy>
     */
    public function fetchPolicies(Uuid $uuid): array
    {
        try {
            $responseJson = $this->fetchData($uuid);

            return $this->mapResponse($responseJson);
        } catch (InvalidSource) {
            // TODO: string is no valid json
        } catch (MappingError) {
            // TODO: Invalid json for mapping
        } catch (TransportExceptionInterface) {
            // TODO: unable to make/finish request
        } catch (HttpExceptionInterface) {
            // TODO: something else went wrong with the request
        }

        return [];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws HttpExceptionInterface
     */
    private function fetchData(Uuid $uuid): string
    {
        $uri     = sprintf('https://sansec.watch/api/magento/%s.json', $uuid->toRfc4122());
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        $response = $this->httpClient->request('GET', $uri, $options);

        return $response->getStatusCode() !== 200
            ? throw new RuntimeException('SOMETHING WENT WRONG')
            : $response->getContent();
    }

    /**
     * @return list<Policy>
     *
     * @throws InvalidSource
     * @throws MappingError
     */
    private function mapResponse(string $json): array
    {
        return (new MapperBuilder())
            ->mapper()
            ->map(
                sprintf('list<%s>', Policy::class),
                Source::json($json)
            );
    }
}
