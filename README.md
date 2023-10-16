# Async consumer

Асинхронный consumer реализованный с помощью Fiber. Для работы необходимо имплементировать PromiseInterface.
Он должен возвращать статус неблокирующей операции, которую можно распараллелить.

В [CurlPromise.php](src%2FPromise%2FCurlPromise.php) пример имплементации PromiseInterface где неблокирующей операцией
является http запрос через curl_multi.

Пример использования CurlPromise:
```php
final class Factory implements RequestFactoryInterface
{
    public function create(): RequestInterface
    {
        return new Request('GET', 'https://www.google.com');
    }
}

final class Handler implements ResponseHandlerInterface
{
    public function handle(ResponseInterface $response): void
    {
        echo sprintf(
            "Response body: %s; response code: %s\n",
            $response->getBody()->getContents(),
            $response->getStatusCode()
        );
    }
}

final class Provider implements ProviderInterface
{
    public function get(): PromiseInterface
    {
        return new CurlPromise(new Factory(), new Handler());
    }
}

(new AsyncConsumer(new Provider(), 5, $logger))->consume();
```
