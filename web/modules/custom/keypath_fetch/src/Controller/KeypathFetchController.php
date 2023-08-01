<?php

namespace Drupal\keypath_fetch\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\UrlGeneratorInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for fetching JSON and displaying it in a template.
 */
class KeypathFetchController extends ControllerBase
{
    /**
     * CustomJsonController constructor.
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     *   The HTTP client.
     */
    public function __construct(
      protected ClientInterface $httpClient,
      protected UrlGeneratorInterface $urlGenerator
    )
    {
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('http_client'),
            $container->get('url_generator'),
        );
    }

    /**
     * Fetch JSON and render it in a template.
     *
     * @return array
     */
    public function build(): array
    {
        $site_root = $this->urlGenerator->generateFromRoute('<front>', [], [
          'absolute' => TRUE,
        ]);
        $endpoint = "$site_root/degrees-rest";
        try {
            $response = $this->httpClient->get($endpoint);

            // Check if the request was successful.
            if ($response->getStatusCode() === 200) {
                $data = Json::decode($response->getBody(), true);
                return [
                  '#theme' => 'keypath_fetch_data',
                  '#data' => $data,
                ];
            } else {
                // Handle non-200 responses.
                return [
                  '#markup' => $this->t('Failed to fetch JSON data.'),
                ];
            }
        }
        catch (\Exception $e) {
            // Handle exceptions
            return [
              '#markup' => $this->t('An error occurred while fetching JSON data.'),
            ];
        }
    }
}
