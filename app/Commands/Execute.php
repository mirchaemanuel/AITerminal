<?php

namespace App\Commands;

use App\Services\AI\OpenAIService;
use App\Traits\OpenAICommand;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;


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
        } catch (Exception $e) {
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
        $prompt = <<<TXT
You are now an ai that takes a prompt and interacts with a computer sending commands and
getting back the output. That means that from now everything you get from this side is
the output of a command. The only exception is if the input starts with "TASK".
In that case i'm asking you what to do. You have to answer only with new commands
to be launched and only one command at a time. Then you wait for the output and, based
on that you send the subsequent command. You MUST NOT add any other information, only the command to be
executed and wait for the output. If to accomplish the task you need more commands, you must send them
one at a time, wait the output and then send the next one. For no reason you must not send more than one or
write anything else of the command to be executed. Sometimes you must guess what operating system or
what capabilities the cli has. If the prompt than doesnt start with "TASK:", it means it is the
output of the previous command.
If there is an error, for no reason you should write anything else of the new command to be executed.
When the task is presumably complete, you must send the command "exit" to terminate the execution, and the prompt will end.
Just answer Ok to this message to acknowledge the task.
TXT;

        $conversation = [
            ['role' => 'system', 'content' => 'assistant'],
            ['role'=> 'user', 'content' => $prompt],
            ['role'=>'assistant', 'content' => 'OK'],
        ];
        $conversation[] = ['role' => 'user', 'content' => 'TASK: ' . $task];

        $client = $openAIService->getClientIstance();

        $exit = false;
        while (!$exit) {
            $response = $client->chat()->create([
                'model' => 'gpt-4',
                'messages' => $conversation
            ]);
            //retrieve the command to execute
            $command = $response->toArray()['choices'][0]['message']['content'];
            if($command === 'exit'){
                $this->info('The task has been completed.');
                $exit = true;
            }
            $this->warn('EXECUTE: ' . $command);
            $processResult = Process::run($command);
            $this->info('OUTPUT: ' . $processResult->output());

            //add the command to the conversation
            $conversation[] = ['role' => 'assistant', 'content' => $processResult->output()];
        }
        return;
    }

    /**
     * Check all prerequisites for OpenAI and inform the user if something is missing
     *
     * @param OpenAIService $openAIService
     * @return bool
     * @throws Exception unable to create .env file
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
