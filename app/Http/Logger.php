<?php

class Logger extends \Monolog\Logger
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;

    protected $logFormat = "{date}\n{level_name}\t{message}\n{context}\n\n";
    protected $dateFormat = "Y-m-d H:i:s";

    public function __construct()
    {
        parent::__construct('API');

        // Handlers
        $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler(storage_path('logs/api.log'), 0, \Monolog\Logger::INFO);
        $rotatingFileHandler->setFormatter(new \Monolog\Formatter\LineFormatter($this->logFormat, $this->dateFormat));
        $this->pushHandler($rotatingFileHandler);
    }

    public function logRequest($request)
    {
        $context = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'parameters' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ];
        $this->info('Request', $context);
    }

    public function logResponse($response)
    {
        $context = [
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'content' => $response->getContent(),
        ];
        $this->info('Response', $context);
    }
}
