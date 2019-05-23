<?php
namespace App\Logging;
use \Illuminate\Log\Logger;
/**
 * Datadogへ対応したログへ整形を行う
 */
class DatadogFormatter
{
    /**
     * APMで出力されたTraceIdをログへ追加
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            // extraへ"url", "ip" ,"http_method", "server", "referrer"を追加
            $handler->pushProcessor(new \Monolog\Processor\WebProcessor());

            // extraへ"file", "line", "class", "function"を追加
            $handler->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());

            // Datadog APMとログを紐付けるために、 "trace_id" を追加
            $handler->pushProcessor(function ($record) {
                $span = \DDTrace\GlobalTracer::get()->getActiveSpan();

                if (null === $span) {
                    return $record;
                }

                $record['dd.trace_id'] = $span->getTraceId();

                return $record;
            });
        }
    }
}
