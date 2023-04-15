# AITerminal

AITerminal is an automation tool for the command line interface (CLI) that interacts with OpenAI's API to execute
commands based on a given task.

![aiterminal3](https://user-images.githubusercontent.com/1971953/232225354-712cde45-3375-4f9b-b540-c7e80b64556a.gif)


## Disclaimer

Please note that AITerminal executes commands on the terminal received from OpenAI. We recommend running the program in
a sandbox environment, as no validation of the received commands is performed, which could pose potential security
risks.

Occasionally, the program may not exit autonomously, requiring you to terminate the execution manually using
CTRL+C.

## Credits

Thanks to _Vincenzo `vuppi` Petrucci_ for the idea and the original prompt. You can reach him
on https://github.com/illegalvuppi

We're Illegal! https://github.com/illegalstudio

## Usage

### Installing composer dependencies

```
composer install
```

or

```
docker run --rm --interactive --tty --volume $PWD:/app composer install --ignore-platform-reqs
```

### Build

You can build the application

```
php ai-terminal app:build
```

It will create a phar executable archive in `/builds`

### Usage

To use AITerminal, follow these steps:

1. Open your terminal or command prompt.
2. Navigate to the directory containing the AITerminal executable.
3. edit the .env file to include your OpenAI API key.
4. Run the following command: `./ai-terminal execute`

After executing the command, you will be prompted to enter the task to be performed. AITerminal will then interact with
the OpenAI API to execute the task using a series of commands.
