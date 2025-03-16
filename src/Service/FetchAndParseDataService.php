<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class FetchAndParseDataService
{
    private LoggerInterface $logger;
    private Client $client;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = new Client();
    }

    public function fetchFilesAndDirectoriesData($log): array
    {
        $this->logFetchEvent($log);
        return $this->parseData();
    }

    public function extractDirectories(array $structuredData): array
    {
        $directories = [];
        $extractDirectories = function ($data, &$directories) use (&$extractDirectories) {
            if (!is_array($data)) {
                return;
            }
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $directories[] = $key;
                    $extractDirectories($value, $directories);
                }
            }
        };

        foreach ($structuredData as $directoriesData) {
            $extractDirectories($directoriesData, $directories);
        }

        return array_values(array_unique($directories));
    }


    public function extractFiles(array $structuredData): array
    {
        $files = [];
        $extractFiles = function ($data, &$files) use (&$extractFiles) {
            if (!is_array($data)) {
                return;
            }
            foreach ($data as $value) {
                if (is_string($value)) {
                    $files[] = $value;
                } elseif (is_array($value)) {
                    $extractFiles($value, $files);
                }
            }
        };

        foreach ($structuredData as $directories) {
            $extractFiles($directories, $files);
        }

        return $files;
    }
    public function extractFilesAndDirectories(array $structuredData): array
    {
        $result = [];

        $extractData = function ($data) use (&$extractData) {
            $output = [];

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $output[$key] = $extractData($value);
                } else {
                    $output[] = $value;
                }
            }

            return $output;
        };

        foreach ($structuredData as $ip => $directories) {
            $result[$ip] = [$extractData($directories)];
        }

        return $result;
    }

    private function parseData(): array
    {
        $response = $this->sendRequest();
        if (!isset($response['items']) || !is_array($response['items'])) {
            $this->logger->error('Invalid API response format', ['response' => $response]);
            return [];
        }

        return $this->transformUrls($response['items']);
    }

    private function transformUrls(array $items): array
    {
        $structuredData = [];

        foreach ($items as $item) {
            $url = rawurldecode($item['fileUrl'] ?? '');
            if (!$url) {
                continue;
            }

            try {
                $parsedUrl = parse_url($url);
                if (!$parsedUrl || !isset($parsedUrl['host']) || !isset($parsedUrl['path'])) {
                    $this->logger->warning('Skipping invalid URL', ['url' => $url]);
                    continue;
                }

                $ip = $parsedUrl['host'];
                $pathParts = explode('/', trim($parsedUrl['path'], '/'));

                if (count($pathParts) < 4) {
                    $this->logger->warning('Skipping short URL', ['url' => $url]);
                    continue;
                }

                if (!isset($structuredData[$ip])) {
                    $structuredData[$ip] = [];
                }

                $current = &$structuredData[$ip];
                foreach ($pathParts as $index => $part) {
                    if ($index === count($pathParts) - 1) {
                        $current[] = $part;
                    } else {
                        if (!isset($current[$part])) {
                            $current[$part] = [];
                        }
                        $current = &$current[$part];
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Error processing URL', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

        $this->logger->info('Data transformation complete', ['timestamp' => time(), 'structuredData' => $structuredData]);

        return $structuredData;
    }
    public function paginate(array $items, int $page, int $limit): array
    {
        $total = count($items);
        $offset = ($page - 1) * $limit;
        $paginatedData = array_slice($items, $offset, $limit);
        $formattedData = array_map(function ($item) {
            return [$item];
        }, $paginatedData);

        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'data' => $formattedData,
        ];
    }


    private function logFetchEvent(string $type): void
    {
        $this->logger->info("New fetch of {$type} data", [
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    private function sendRequest(): array
    {
        $url = $_ENV['TEST_URL'];
        try {
            $response = $this->client->get($url);
            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Request failed with status code: ' . $response->getStatusCode());
                return [];
            }
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Failed to decode JSON response', ['error' => json_last_error_msg()]);
                return [];
            }
            return $data;
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch URLs', ['error' => $e->getMessage()]);
            return [];
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch URLs', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
