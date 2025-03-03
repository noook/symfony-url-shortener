<?php

namespace App\Command\User;

use App\DTO\CreateUserDTO;
use App\Service\AuthService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user:create',
    description: 'Creates a user',
)]
class CreateUserCommand extends Command
{
    // Use constants so that we can references these when defining AND retrieving arguments/options
    // It's less error-prone than using strings directly
    private const EMAIL = 'email';
    private const DISPLAY_NAME = 'display-name';
    private const PASSWORD = 'password';

    // Inject services in the constructor
    public function __construct(
        private AuthService $authService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::EMAIL, InputArgument::REQUIRED, 'User email')
            ->addOption(self::DISPLAY_NAME, null, InputOption::VALUE_OPTIONAL, 'User display name')
            ->addOption(self::PASSWORD, null, InputOption::VALUE_OPTIONAL, 'User password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument(self::EMAIL);
        // The following options are optional before the command execution, but required during the command.
        // Ask the user for the data if it's not provided
        $displayName = $input->getOption(self::DISPLAY_NAME);
        $password = $input->getOption(self::PASSWORD);

        // Pressing enter without providing a value will result in a null value
        while ($displayName === null) {
            $displayName = $io->ask('What is your display name?');
        }

        while ($password === null) {
            $password = $io->askHidden('What is your password?');
        }

        // $userDto is not validated, just constructed. It should be validated.
        $userDto = new CreateUserDTO($email, $displayName, $password);

        // The following code might throw an error if the user already exists (username or email already in use)
        try {
            $user = $this->authService->registerUser($userDto);
            // Debug user
            dump($user);
            $io->success(
                sprintf("Successfully created user %s", $user->getEmail()),
            );
            return Command::SUCCESS;
        } catch(Exception $e) {
            $io->error(
                sprintf("Failed to create user %s", $email),
            );
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
