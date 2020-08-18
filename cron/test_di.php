<?php
// https://symfony.com/doc/current/service_container/3.3-di-changes.html#autowiring-by-default-use-type-hint-instead-of-service-id
// https://github.com/PHP-DI/demo


require_once dirname(__DIR__ ). '/vendor/autoload.php';

class InvoiceGenerator
{

}

class InvoiceMailer
{
    private $generator;

    public function __construct(InvoiceGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function test()
    {
        echo 'test';
    }
}

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();
$container->register('invoice_generator', InvoiceGenerator::class);
$container->register('invoice_mailer', InvoiceMailer::class)
    ->setArguments([new Reference('invoice_generator')]);

$newsletterManager = $container->get('invoice_mailer');
$newsletterManager->test();

//use Model\Cycle\AzeriVocabulary;
//use Telegram\Bot\Api;
//
//
//try {
//    $vocabulary = new AzeriVocabulary();
//}
//catch (Throwable $exception)
//{
//    print_r($exception->getMessage());
//    echo "\n";
//}