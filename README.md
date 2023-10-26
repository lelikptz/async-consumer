# Async consumer

Асинхронный consumer реализованный с помощью Fiber. Для работы необходимо имплементировать TaskInterface.
Реализация должна возвращать статус неблокирующей операции, которую можно распараллелить.

В [Task.php](src%2FTask%2FHttp%2FTask.php) пример имплементации TaskInterface где неблокирующей
операцией является http запрос через guzzle.

## Пример использования Http\Task:

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

    public function onException(RequestException $exception): void
    {
        $this->logger->error($exception->getMessage());
    }
}
```

Провайдер задач собирает необходимую таску и возвращает её в консьюмер по мере готовности:

```php
final class Provider implements ProviderInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function get(): array
    {
        return [
            new Task(new Factory($this->logger), new Handler($this->logger)),
        ];
    }
}
```

Собираем консьюмер и запускаем как демон например через супервизор.

$pollTimeoutInMicroseconds - дэлэй между опросами провайдера

```php
$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));
(new AsyncConsumer(new Provider($logger), new FiberExecutor(), $pollTimeoutInMicroseconds, $logger))->consume();
```

## Пример использования rabbitmq как провайдера задач:

Для использования [AMPQProvider.php](src%2FProvider%2FAMPQProvider.php) имплементируем TransformerInterface для создания
TaskInterface из сообщения AMQPMessage:

```php
final class Transformer implements TransformerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function transform(AMQPMessage $message): TaskInterface
    {
        return new Task(new Factory($this->logger), new Handler($this->logger));
    }
}
```

Собираем и запускаем:

$maxBatchSize - максимальный размер батча, который будем собирать из rabbitmq и по факту количество распараллеленных
задач

$maxBatchCollectTimeInSeconds - время, которое ждём пока батч собирается из rabbitmq, если оно вышло запускам обработку
с тем, что есть

$pollTimeoutInMicroseconds - дэлэй между опросами провайдера

```php
$connection = new AMQPStreamConnection('localhost', '5672', 'guest', 'guest');
$provider = new AMPQProvider($connection, 'provider', new Transformer($logger));
$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));
$batch = new BatchProvider($provider, 10, 5, $pollTimeoutInMicroseconds);

(new AsyncConsumer($batch, new FiberExecutor(), $pollTimeoutInMicroseconds, $logger))->consume();
```
