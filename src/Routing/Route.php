<?php


namespace TomHart\Restful\Routing;

use TomHart\Restful\Exceptions\UndefinedIndexException;

class Route
{
    private $method;

    private $hrefs = [];

    /**
     * Route constructor.
     * @param $method
     * @param array $hrefs
     */
    public function __construct($method, array $hrefs)
    {
        $this->method = $method;
        $this->hrefs = $hrefs;
    }


    /**
     * Build an instance from an array.
     * @param $arr
     * @return static
     */
    public static function fromArray($arr): self
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
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $part
     * @param array $queryString
     * @return array|string
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
