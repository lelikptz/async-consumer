# Async consumer

Асинхронный consumer реализованный с помощью Fiber. Для работы необходимо имплементировать PromiseInterface.
Он должен возвращать статус неблокирующей операции, которую можно распараллелить.

В [GuzzlePromise.php](src%2FPromise%2FGuzzlePromise.php) пример имплементации PromiseInterface где неблокирующей
операцией
является http запрос через guzzle.

Пример использования GuzzlePromise:

Имплементируем фабрику для создания реквеста:

```php
final class Factory implements RequestFactoryInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function create(): RequestInterface
    {
        $this->logger->info('Some logic for creating request');
        
        return new Request('GET', 'https://www.google.com');
    }
}
```

Имплементируем handler для обработки респонса и ошибки:

```php
final class Handler implements ResponseHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function onSuccess(ResponseInterface $response): void
    {
        $this->logger->info(
            sprintf(
                "Response body: %s; response code: %s",
                $response->getBody()->getContents(),
                $response->getStatusCode()
            )
        );
        $this->logger->info('Some logic with response');
        $this->logger->info('Finish');
    }

    public function onException(PromiseException $exception): void
    {
        $this->logger->error($exception->getMessage());
    }
}
```

Провайдер задач собирает необходимый promise и возвращает его в консьюмер по мере готовности:

```php
final class Provider implements ProviderInterface
{
    public function get(): ?PromiseInterface
    {
        return new CurlPromise(new Factory(), new Handler());
    }
}
```

Собираем консьюмер и запускаем как демон например через супервизор.

$concurrency - размер батча запросы которого будут выполняться параллельно.

$maxBatchCollectTimeInSeconds - время, которое будем ждать пока провайдер не выдаст количество задач, равное
$concurrency.

```php
$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));
(new AsyncConsumer(new Provider($logger), $concurrency, $maxBatchCollectTimeInSeconds, $logger))->consume();
```
