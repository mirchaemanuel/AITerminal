<?php

namespace App\Commands;

use App\Exceptions\MissingDotEnvFileException;
use App\Exceptions\MissingOpenAIKeyException;
use App\Services\AI\OpenAIService;
use App\Traits\OpenAICommand;
use App\Utils\CheckDotEnv;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Process;


class Execute extends Command
{
    use OpenAICommand;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'execute';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'let the AI execute a task in the terminal';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(OpenAIService $openAIService)
    {
        try {
            if (!$this->checkOpenAI($openAIService)) {
                return;
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        /*
         * ask for the task
         */
        $task = $this->ask('What task should the AI accomplish?');
        //check if the task is empty
        if (empty($task)) {
            $this->error('The task is empty. Please try again.');
            return;
        }
        //check if the task is too long
        if (strlen($task) > 2048) {
            $this->error('The task is too long. Please try again.');
            return;
        }
        /*
         * ask the user if it is ok to execute the task
         */
        $confirm = $this->confirm('Do you want the AI to execute the task?');
        if (!$confirm) {
            $this->error('The task was not executed.');
            return;
        }

        /*
         * This prompt has been suggested by Vuppi @IllegalStudio
         */
        $aiRole = <<<TXT
you are now an ai that takes a prompt and interacts with a computer sending commands and getting back the output. that means that from now everything you get from this side is the output of a command. the only exception is if the input starts with 
prompt: 
In that case i'm asking you what to do. You have to answer only with new commands to be launched and only one command at a time. Then you wait for the output and, based on that you send the subsequent command. Just answer Ok to this message to ackwnoloedge the task.
TXT;

        $openAIService->buildClient();


        return;
    }

    /**
     * Check all prerequisites for OpenAI and inform the user if something is missing
     *
     * @param OpenAIService $openAIService
     * @return bool
     * @throws \Exception unable to create .env file
     */

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
