<?php

namespace TomHart\Restful;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use TomHart\Restful\Concerns\Restful;
use TomHart\Restful\Concerns\Transformer;
use TomHart\Restful\Routing\Route;

use function GuzzleHttp\json_decode as guzzle_json_decode;

class Builder
{
    /**
     * @var Restful
     */
    protected $model;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var mixed[]
     */
    protected $wheres = [];

    /**
     * @var string[]
     */
    private $order;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int[]
     */
    private $goodStatusCodes = [
        Response::HTTP_OK,
        Response::HTTP_CREATED,
        Response::HTTP_ACCEPTED
    ];
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Builder constructor.
     * @param Client $client
     * @param LoggerInterface $logger
     * @param Restful $model
     */
    public function __construct(Client $client, LoggerInterface $logger, Restful $model)
    {
        $this->model = $model;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * What model are we querying?
     * @param string $class
     * @return Builder
     */
    public static function model(string $class)
    {
        $class = new $class();
        return app(static::class, ['model' => $class]);
    }

    private function newModelInstance(): Model
    {
        return new $this->model();
    }

    /**
     * Get the index route for the model via the options route.
     * @param string $route
     * @param null $id
     * @return Route
     * @throws Exceptions\UndefinedIndexException
     * @throws RouteNotFoundException
     */
    private function getModelRoute(string $route, $id = null): Route
    {
        $optionsUrl = $this->model->getOptionsUrl();
        $optionRoute = new Route('OPTIONS', ['absolute' => $optionsUrl]);
        $links = $this->getResponse($optionRoute, ['id' => $id]);
        if (!$links) {
            throw new RouteNotFoundException('Cannot get options from route');
        }

        $links = $this->parseJsonFromResponse($links);
        if (!isset($links[$route])) {
            throw new RouteNotFoundException("Cannot find link to $route route");
        }

        return Route::fromArray($links[$route]);
    }

    /**
     * Get the JSON from the given URL.
     * @param Route $route
     * @param array $queryString
     * @param array $postData
     * @return MessageInterface
     * @throws Exceptions\UndefinedIndexException
     */
    private function getResponse(Route $route, array $queryString = [], array $postData = []): ?MessageInterface
    {
        if ($this->shouldLog()) {
            $this->logger->info(
                sprintf('REST-CALL: %s to %s', $route->getMethod(), $route->getHrefs('absolute', $queryString))
            );
        }

        return $this->client->request(
            $route->getMethod(),
            $route->getHrefs('absolute', $queryString),
            [
                'headers' => config('restful.headers'),
                'form_params' => $postData
            ]
        );
    }

    /**
     * Extract JSON from a Response.
     * @param MessageInterface $response
     * @return mixed
     * @throws Exceptions\UndefinedIndexException
     */
    private function parseJsonFromResponse(MessageInterface $response)
    {
        $body = $response->getBody()->getContents();
        if (!$body) {
            return null;
        }

        $json = guzzle_json_decode($body, true);

        // If the response is paginated, and there is a next page, get that and add the results.
        if ($this->isPaginatedResponse($json) &&
            isset($json['next_page_url'], $json['current_page'], $json['last_page']) &&
            $json['current_page'] < $json['last_page']
        ) {
            $nextPageResponse = $this->getResponse(Route::fromUrl($json['next_page_url'], 'GET'));
            if ($nextPageResponse) {
                $nextPageJson = $this->parseJsonFromResponse($nextPageResponse);

                $json['data'] = array_merge($json['data'], $nextPageJson['data']);

                return $json;
            }
        }

        return $json;
    }

    /**
     * Is the JSON responses a paginatable one?
     * @param mixed[] $json
     * @return bool
     */
    private function isPaginatedResponse(array $json): bool
    {
        return empty(array_diff(config('restful.pagination_json_keys'), array_keys($json)));
    }

    /**
     * Extract data from the JSON
     * @param $json
     * @return array
     * @throws InvalidArgumentException
     */
    private function extractDataFromJson($json): array
    {
        if (!isset($json['data'])) {
            throw new InvalidArgumentException('$json doesn\'t contain a data key');
        }

        return $json['data'];
    }

    /**
     * Add a where clause.
     * @param string $column
     * @param $value
     * @return Builder
     */
    public function where(string $column, $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'type' => 'Basic',
            'value' => $value
        ];

        return $this;
    }

    /**
     * Add a whereIn clause.
     * @param string $column
     * @param mixed[] $values
     * @return Builder
     */
    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'type' => 'In',
            'values' => $values
        ];

        return $this;
    }

    /**
     * Return all the wheres
     * @return mixed[]
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * Set an order
     * @param string $column
     * @param string $direction
     * @return $this\
     */
    public function order(string $column, string $direction): self
    {
        $this->order = [
            'column' => $column,
            'direction' => $direction
        ];

        return $this;
    }

    /**
     * Get the order
     * @return array|null
     */
    public function getOrder(): ?array
    {
        return $this->order;
    }

    /**
     * Specify a limit
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the limit
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Call the API, and return the models.
     * @return Collection
     * @throws Exceptions\UndefinedIndexException
     */
    public function get(): Collection
    {
        $options = $this->getModelRoute('index');

        /** @var Transformer $transformer */
        $transformer = app(Transformer::class);

        $queryString = $transformer->buildQueryString($this);

        $response = $this->getResponse($options, $queryString);
        if (!$response) {
            return collect();
        }

        $json = $this->parseJsonFromResponse($response);
        if (!$json) {
            return collect();
        }

        $data = $this->extractDataFromJson($json);

        $instance = $this->newModelInstance();

        return $instance->newCollection(
            array_map(
                function ($item) use ($instance) {
                    return $instance->newFromBuilder($item);
                },
                $data
            )
        );
    }

    /**
     * @param string $route
     * @param mixed[] $data
     * @param mixed $id
     * @return bool
     * @throws Exceptions\UndefinedIndexException
     */
    private function _call(string $route, array $data, $id = null): bool
    {
        $route = $this->getModelRoute($route, $id);
        $response = $this->getResponse($route, [], $data);
        if (!$response) {
            return false;
        }

        return in_array($response->getStatusCode(), $this->goodStatusCodes, true);
    }

    /**
     * @param mixed[] $data
     * @return bool
     * @throws Exceptions\UndefinedIndexException
     */
    public function insert(array $data): bool
    {
        return $this->_call('store', $data);
    }

    /**
     * @param mixed $id
     * @param mixed[] $data
     * @return bool
     * @throws Exceptions\UndefinedIndexException
     */
    public function update($id, array $data): bool
    {
        return $this->_call('update', $data, $id);
    }

    /**
     * @param mixed $id
     * @return bool
     * @throws Exceptions\UndefinedIndexException
     */
    public function delete($id): bool
    {
        return $this->_call('destroy', [], $id);
    }

    /**
     * Should we log requests?
     * @return bool
     */
    private function shouldLog(): bool
    {
        return !!config('restful.logging', false);
    }
}
