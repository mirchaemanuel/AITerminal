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
You are an AI that receives a prompt and interacts with a computer by sending commands and receiving output.
From now on, every input you receive, except those starting with "TASK", is the output of a command. 
If the input starts with "TASK", I am asking you what to do. Respond with a single command to be executed. 
Wait for the output and, based on that, determine if the task is completed or if another command is needed. 
ONLY send the command to be executed. Avoid commands that might run indefinitely, such as "ping" without a limit.
Send commands one at a time, wait for the output, and then send the next one if necessary. Sometimes, you must guess 
the operating system or the CLI's capabilities. If the input doesn't start with "TASK:", it's the output 
of the previous command. Note that a command might have no output. In this case, you receive this message: 
`CMD::OK` if successful, `CMD::KO` if unsuccessful. If successful, determine if the task is complete or if you need to
proceed with the next command. If there's an error, send the new command to be executed.
Check if the task is completed before sending additional commands. When the task appears complete, send the "exit" command to 
terminate the execution, and the prompt will end.
Reply "Ok" to this message to acknowledge the task.

TXT;

        $conversation = [
            ['role' => 'system', 'content' => 'assistant'],
            ['role' => 'user', 'content' => $prompt],
            ['role' => 'assistant', 'content' => 'OK'],
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
            if ($command === 'exit') {
                $this->info('The task has been completed.');
                $exit = true;
            }else {
                $this->warn('EXECUTE: ' . $command);
                $processResult = Process::run($command);
                if (empty($output = $processResult->output())) {
                    $output = $processResult->successful() ? 'CMD::OK' : $processResult->errorOutput();
                    if (empty($output)) {
                        $output = 'CMD::KO';
                    }
                }
                $this->info('OUTPUT: ' . $output);

                //add the command to the conversation
                $conversation[] = ['role' => 'assistant', 'content' => $output];
            }
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
