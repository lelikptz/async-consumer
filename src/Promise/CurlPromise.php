<?php

declare(strict_types=1);

namespace lelikptz\AsyncConsumer\Promise;

use CurlHandle;
use CurlMultiHandle;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class CurlPromise implements PromiseInterface
{
    private bool $wasHandle = false;

    private int $active = 0;

    private CurlHandle $handler;

    private CurlMultiHandle $multiHandler;

    public function __construct(
        private readonly RequestFactoryInterface $factory,
        private readonly ResponseHandlerInterface $responseHandler,
    ) {
        $this->handler = curl_init();

        $request = $this->factory->create();
        curl_setopt($this->handler, CURLOPT_URL, $request->getUri());
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handler, CURLOPT_HEADER, true);
        $headers = [];
        foreach ($request->getHeaders() as $key => $value) {
            $headers[] = $key . ': ' . implode(', ', $value);
        }

        curl_setopt($this->handler, CURLOPT_HTTPHEADER, $headers);

        switch ($request->getMethod()) {
            case 'GET':
                curl_setopt($this->handler, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->handler, CURLOPT_POSTFIELDS, $request->getBody()->getContents());
                curl_setopt($this->handler, CURLOPT_POST, true);
                break;
        }

        $this->multiHandler = curl_multi_init();
        curl_multi_add_handle($this->multiHandler, $this->handler);

        curl_multi_exec($this->multiHandler, $this->active);
    }

    public function getStatus(): Status
    {
        if ($this->active && curl_multi_exec($this->multiHandler, $this->active) == CURLM_OK) {
            return Status::PENDING;
        }

        if (!$this->wasHandle) {
            $this->responseHandler->handle($this->getResponse());
            $this->wasHandle = true;
        }

        return Status::OK;
    }

    private function getResponse(): ResponseInterface
    {
        $code = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
        if (!is_int($code)) {
            throw new RuntimeException('Curl error: ' . curl_error($this->handler));
        }
        $headerSize = intval(curl_getinfo($this->handler, CURLINFO_HEADER_SIZE));
        $response = curl_multi_getcontent($this->handler);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $headers = [];
        foreach (explode("\r\n", $header) as $key => $line) {
            if ($key >= 1 && strlen(trim($line, " \r\n"))) {
                [$key, $value] = explode(': ', trim($line, " \r\n"));
                $headers[$key] = $value;
            }
        }

        return new Response($code, $headers, $body);
    }

    public function __destruct()
    {
        curl_multi_remove_handle($this->multiHandler, $this->handler);
        curl_multi_close($this->multiHandler);
    }
}
