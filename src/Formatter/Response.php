<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Formatter;

class Response
{
    /**
     * @var array
     */
    private $rawResponse;

    /**
     * @var Result[]
     */
    private $results;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $rawResponse
     */
    public function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;

        if (isset($rawResponse['errors'])) {
            if (!empty($rawResponse['errors'])) {
                $this->errors = $rawResponse['errors'][0];
            }
        }
    }

    /**
     * @return string
     */
    public function getJsonResponse()
    {
        return json_encode($this->rawResponse);
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @param Result $result
     */
    public function addResult(Result $result)
    {
        $this->results[] = $result;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        if (null !== $this->results && !$this->results instanceof Result) {
            reset($this->results);

            return $this->results[0];
        }

        return $this->results;
    }

    /**
     * @return Result[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param Result $result
     */
    public function setResult(Result $result)
    {
        $this->results = $result;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return bool
     */
    public function containsResults()
    {
        return isset($this->rawResponse['results']) && !empty($this->rawResponse['results']);
    }

    /**
     * @return bool
     */
    public function containsRows()
    {
        return isset($this->rawResponse['results'][0]['columns']) && !empty($this->rawResponse['results']['0']['columns']);
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->rawResponse;
    }
}
