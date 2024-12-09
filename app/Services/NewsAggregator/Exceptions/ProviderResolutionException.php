<?php


namespace App\Services\NewsAggregator\Exceptions;


use Exception;

class ProviderResolutionException extends \RuntimeException
{
    /**
     * @var ?Exception
     */
    private ?Exception $previous;

    /**
     * ProviderResolutionException constructor.
     *
     * @param $message
     * @param $code
     * @param ?Exception $previous
     */
    public function __construct($message, int $code = 0, Exception $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;

        parent::__construct($message, $code, $previous);
    }

    public static function forIdentifier(string $identifier, Exception $previous = null) {
        return new static(sprintf('Provider [%s] could not be resolved.', $identifier), 0, $previous);
    }
}
