<?php
    require __DIR__ . '/../vendor/autoload.php';

    use App\Controllers\ReportController;
    use Nyholm\Psr7\Factory\Psr17Factory;
    use Nyholm\Psr7Server\ServerRequestCreator;
?>

<?php

        $publicDir = __DIR__;
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $staticFile = realpath($publicDir . $requestUri);

        if ($staticFile && is_file($staticFile)) {
            $fileExtension = pathinfo($staticFile, PATHINFO_EXTENSION);
            $contentTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
            ];

            $contentType = $contentTypes[$fileExtension] ?? 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            readfile($staticFile);
            exit;
        }

    $psr17Factory = new Psr17Factory();
    $creator = new ServerRequestCreator(
        $psr17Factory,
        $psr17Factory,
        $psr17Factory,
        $psr17Factory
    );
    $request = $creator->fromGlobals();
    $path = $request->getUri()->getPath();

    switch ($path) {
        case '/':
            $response = $psr17Factory->createResponse(404);
            $response->getBody()->write("404 - Page Not Found");
            break;
        default:
            $controller = new ReportController();
            $response = $controller->renderReports($request, $psr17Factory->createResponse());
    }

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("{$name}: {$value}", false);
        }
    }
    http_response_code($response->getStatusCode());
    echo $response->getBody();
    ?>