<?php


namespace TomHart\Restful\Routing;

use TomHart\Restful\Exceptions\UndefinedIndexException;

final class Route
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var string[]
     */
    private $hrefs = [];

    /**
     * Route constructor.
     * @param string $method
     * @param string[] $hrefs
     */
    public function __construct(string $method, array $hrefs)
    {
        $this->method = $method;
        $this->hrefs = $hrefs;
    }


    /**
     * Build an instance from an array.
     * @param mixed[] $arr
     * @return static
     */
    public static function fromArray(array $arr): self
    {
        return new static($arr['method'], $arr['href']);
    }

    /**
     * @param string $url
     * @param string $method
     * @return static
     */
    public static function fromUrl(string $url, string $method): self
    {
        return new static($method, ['absolute' => $url]);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $part
     * @param mixed[] $queryString
     * @return string[]|string
     * @throws UndefinedIndexException
     */
    public function getHrefs(string $part = null, array $queryString = [])
    {
        if ($part !== null) {
            if (!isset($this->hrefs[$part])) {
                throw new UndefinedIndexException("index $part not in hrefs");
            }

            $post = '';
            if (!empty($queryString)) {
                $post = '?' . http_build_query($queryString);
            }

            return $this->hrefs[$part] . $post;
        }
        return $this->hrefs;
    }
}
