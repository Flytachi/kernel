<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\Blink;

use Flytachi\Kernel\Src\Factory\Stereotype;

/**
 * Class Blink
 *
 * `Blink` is a class for abstracting interactions with a cURL session.
 * It simplifies HTTP request operations such as GET, POST, PUT, DELETE, and PATCH.
 *
 * The methods provided by `Blink` include:
 *
 * - `authBearer(string $token): string`: Generates a bearer authentication string from a token.
 * - `retry(int $count, int $timeout = 30): static`: Specifies the retry count and timeout for the session.
 * - `headers(string ...$headers): static`: Sets request headers.
 * - `get(string $url, null|array $params = null): static`: Sends an HTTP GET request.
 * - `put(string $url, null|array $params = null): static`: Sends an HTTP PUT request.
 * - `post(string $url, null|array $params = null, null|array $body = null): static`: Sends an HTTP POST request.
 * - `delete(string $url, null|array $params = null): static`: Sends an HTTP DELETE request.
 * - `patch(string $url, null|array $params = null): static`: Sends an HTTP PATCH request.
 * - `request(string $method, string $url, null|array $params = null): static`: Sends a custom HTTP request.
 * - `body(array $body, string $type = 'json'): static`: Specifies the request body.
 * - `send(bool $isThrowable = true): Blink
 *
 *  Constants:
 *  @const ACCEPT_JSON
 *  @const CONTENT_JSON
 *
 *
 *  Methods:
 *  @method  self         authBearer(string $token)
 *  @method  self         retry(int $count, int $timeout = 30)
 *  @method  self         headers(string ...$headers)
 *  @method  self         get(string $url, null|array $params = null)
 *  @method  self         put(string $url, null|array $params = null)
 *  @method  self         post(string $url, null|array $params = null)
 *  @method  self         delete(string $url, null|array $params = null)
 *  @method  self         patch(string $url, null|array $params = null)
 *  @method  self         request(string $method, string $url, null|array $params = null)
 *  @method  self         body(mixed $body)
 *  @method  self         bodyJson(array|string $bodyJson)
 *
 *  @version 2.5
 *  @author Flytachi
 */
final class Blink extends Stereotype
{
    public const string ACCEPT_JSON = 'Accept: application/json';
    public const string CONTENT_JSON = 'Content-Type: application/json';

    private static null|self $blink = null;
    private \CurlHandle $curl;
    private int $maxRetry = 1;

    public function __construct()
    {
        parent::__construct();
        $this->curl = curl_init();
    }

    /**
     * Returns the Authorization header value with the Bearer token.
     *
     * @param string $token The Bearer token to include in the Authorization header.
     * @return string The Authorization header value with the Bearer token.
     */
    public static function authBearer(string $token): string
    {
        return 'Authorization: Bearer ' . $token;
    }

    private static function setOption(int $option, mixed $value): void
    {
        if (self::$blink == null) {
            self::$blink = new Blink();
            self::setOption(CURLOPT_RETURNTRANSFER, true);
            self::setOption(CURLOPT_ENCODING, '');
            self::setOption(CURLOPT_MAXREDIRS, 10);
            self::setOption(CURLOPT_TIMEOUT, 10);
            self::setOption(CURLOPT_FOLLOWLOCATION, true);
            self::setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }
        curl_setopt(self::$blink->curl, $option, $value);
    }

    /**
     * Retries the request a specified number of times with a specified timeout.
     *
     * @param int $count The number of times to retry the request.
     * @param int $timeout Optional. The maximum time in seconds to wait for each request.
     *                     Defaults to 30 if no timeout is provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function retry(int $count, int $timeout = 30): self
    {
        self::setOption(CURLOPT_TIMEOUT, $timeout);
        self::$blink->maxRetry = $count;
        return self::$blink;
    }

    /**
     * Sets the headers for the HTTP request.
     *
     * @param string ...$headers The headers to be set for the HTTP request.
     * Multiple headers can be passed as separate string arguments.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function headers(string ...$headers): self
    {
        self::setOption(CURLOPT_HTTPHEADER, $headers);
        return self::$blink;
    }

    /**
     * Makes a GET request to a given URL.
     *
     * @param string $url The URL to send the GET request to.
     * @param null|array $params Optional. The parameters to include in the URL query string.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function get(string $url, null|array $params = null): self
    {
        return self::request('GET', $url, $params);
    }

    /**
     * Makes a PUT request to a given URL.
     *
     * @param string $url The URL to send the PUT request to.
     * @param null|array $params Optional. The parameters to include in the URL query string.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function put(string $url, null|array $params = null): self
    {
        return self::request('PUT', $url, $params);
    }

    /**
     * Makes a POST request to a given URL with optional parameters and body.
     *
     * @param string $url The URL to send the POST request to.
     * @param null|array $params Optional. The parameters to include in the URL query string.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function post(string $url, null|array $params = null): self
    {
        return self::request('POST', $url, $params);
    }

    /**
     * Sends a DELETE request to a specified URL.
     *
     * @param string $url The URL to send the DELETE request to.
     * @param null|array $params Optional. The parameters to include in the URL query string.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function delete(string $url, null|array $params = null): self
    {
        return self::request('DELETE', $url, $params);
    }

    /**
     * Makes a PATCH request to a given URL.
     *
     * @param string $url The URL to send the PATCH request to.
     * @param null|array $params Optional. The parameters to include in the URL query string for the PATCH request.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function patch(string $url, null|array $params = null): self
    {
        return self::request('PATCH', $url, $params);
    }

    /**
     * Makes a request to a given URL using a specified HTTP method.
     *
     * @param string $method The HTTP method to use for the request.
     * @param string $url The URL to send the request to.
     * @param null|array $params Optional. The parameters to include in the URL query string.
     *                          Defaults to null if no parameters are provided.
     * @return self Returns an instance of the class that this method belongs to.
     */
    public static function request(string $method, string $url, null|array $params = null): self
    {
        self::setOption(CURLOPT_CUSTOMREQUEST, $method);
        if ($params == null) {
            self::setOption(CURLOPT_URL, $url);
        } else {
            self::setOption(CURLOPT_URL, $url . '?' . http_build_query($params));
        }
        return self::$blink;
    }

    /**
     * Sets the request body for the HTTP request.
     *
     * @param array $body The data to be sent as the request body.
     * @return self Returns the current instance of the Blink class.
     */
    public static function body(mixed $body): self
    {
        self::setOption(CURLOPT_POSTFIELDS, $body);
        return self::$blink;
    }

    /**
     * Sets the request body for the HTTP request (json).
     *
     * @param array|string $bodyJson The data to be sent as the request body.
     * @return self Returns the current instance of the Blink class.
     */
    public static function bodyJson(array|string $bodyJson): self
    {
        if (is_array($bodyJson)) {
            self::setOption(CURLOPT_POSTFIELDS, json_encode($bodyJson));
        } else {
            self::setOption(CURLOPT_POSTFIELDS, $bodyJson);
        }
        return self::$blink;
    }

    /**
     * Send a request using cURL and return the response.
     *
     * @param bool $isThrowable Optional. Flag indicating whether to throw
     * an exception on request failure. Default is true.
     * @return BlinkObject The response object containing the cURL information and the response content.
     * @throws BlinkException
     */
    public function send(bool $isThrowable = true): BlinkObject
    {
        $info = curl_getinfo($this->curl);
        $response = null;

        while (true) {
            if ($this->maxRetry == 0) {
                break;
            }

            $this->logger->debug("Blink Send Request: {$info['url']}");
            $response = curl_exec($this->curl);
            $info = curl_getinfo($this->curl);
            if ($info['http_code'] === 0) {
                $info['http_code'] = 504;
            }

            if ($info['http_code'] >= 400) {
                if ($this->maxRetry > 1 && $info['http_code'] == 504) {
                    --$this->maxRetry;
                    continue;
                }
                if ($isThrowable) {
                    throw new BlinkException("Blink Request '{$info['url']}' status => {$info['http_code']}");
                }
            }

            $this->logger->debug("Blink Send Response: [status:{$info['http_code']}] {$info['url']}");
            break;
        }

        curl_close($this->curl);
        self::$blink = null;
        return new BlinkObject([
            ...$info,
            'response' => $response ?: null
        ]);
    }
}
