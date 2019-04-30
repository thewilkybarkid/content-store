<?php

declare(strict_types=1);

namespace tests\Libero\ContentStore;

use Doctrine\DBAL\Connection;
use Libero\ContentApiBundle\Adapter\DoctrineItems;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function ob_get_clean;
use function ob_start;

abstract class KernelTestCase extends BaseKernelTestCase
{
    protected static function bootKernel(array $options = [])
    {
        parent::bootKernel($options);

        /** @var Connection $connection */
        $connection = self::$container->get('doctrine.dbal.default_connection');
        /** @var DoctrineItems $items */
        $items = self::$container->get('libero.content_store.items');

        foreach ($items->getSchema()->toSql($connection->getDatabasePlatform()) as $query) {
            $connection->exec($query);
        }

        return self::$kernel;
    }

    final protected function handle(Request $request) : Response
    {
        ob_start();
        $response = self::$kernel->handle($request);
        $content = ob_get_clean();

        if (!$response instanceof StreamedResponse) {
            return $response;
        }

        return new Response($content, $response->getStatusCode(), $response->headers->all());
    }
}
