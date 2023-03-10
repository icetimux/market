<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand extends Command
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);

        $user->setEmail($email);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>User created successfully!</info>');

        return Command::SUCCESS;
    }
}