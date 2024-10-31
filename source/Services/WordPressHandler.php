<?php
namespace Setka\Workflow\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Class WordPressHandler
 */
class WordPressHandler
{
    public function __invoke(RequestInterface $request, array $options)
    {
        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }

        $args = array(
            'method' => $request->getMethod(),
        );

        if ($args['method'] != 'GET') {
            $args['body'] = (string) $request->getBody();
        }

        $headers = array();
        if ($request->hasHeader('Content-Type')) {
            $_a = $request->getHeader('Content-Type');

            $headers['Content-Type'] = current($_a);
            unset($_a);
        }
        $args['headers'] = $headers;
        unset($headers);

        $response = wp_remote_request($request->getUri(), $args);

        // Can't connect or something similar.
        if (is_wp_error($response)) {
            throw new ConnectException($response->get_error_message(), $request);
        }

        if (!is_array($response)) {
            throw new ConnectException('WordPress HTTP API returns unknown response.', $request);
        }

        if (is_array($response['headers'])) {
            $responseHeaders = $response['headers'];
        } else {
            $responseHeaders = $response['headers']->getAll();
        }

        return new Response(
            $response['response']['code'],
            $responseHeaders,
            $response['body']
        );
    }
}
