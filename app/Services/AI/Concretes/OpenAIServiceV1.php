<?php

namespace App\Services\AI\Concretes;

use App\Exceptions\MissingDotEnvFileException;
use App\Exceptions\MissingOpenAIKeyException;
use App\Services\AI\OpenAIService;
use App\Utils\CheckDotEnv;
use OpenAI;
use OpenAI\Client as OpenAIClient;

/**
 * OpenAI Service
 *
 *
 */
class OpenAIServiceV1 implements OpenAIService
{
    private OpenAIClient $client;

    public function __construct()
    {

    }

    /**
     * @return OpenAIServiceV1 ready to be used
     *
     * @throws MissingDotEnvFileException
     * @throws MissingOpenAIKeyException
     */
    public function buildClient(): OpenAIServiceV1
    {
        //check if .env file exists, otherwise create it
        if (CheckDotEnv::exists() === false) {
            throw new MissingDotEnvFileException();
        }

        //check .env file
        $openaiKey = config('ai.openai.api_key');
        if (empty($openaiKey)) {
            throw new MissingOpenAIKeyException();
        }

        $this->client = OpenAI::client($openaiKey);

        return $this;
    }

    public function getClientIstance(): OpenAIClient
    {
        return $this->client;
    }

}

