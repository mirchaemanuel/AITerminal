<?php

namespace App\Services\AI;


use App\Exceptions\MissingDotEnvFileException;
use App\Exceptions\MissingOpenAIKeyException;
use OpenAI\Client as OpenaAIClient;

/**
 * OpenAI Service
 *
 *
 */
interface OpenAIService
{
    /**
     * @return OpenAIService ready to be used
     *
     * @throws MissingDotEnvFileException
     * @throws MissingOpenAIKeyException
     */
    public function buildClient(): OpenAIService;

    public function getClientIstance(): OpenaAIClient;

}
