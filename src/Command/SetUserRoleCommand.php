<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:set-user-role',
    description: 'Assign a role to a user by email',
)]
class SetUserRoleCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('role', InputArgument::REQUIRED, 'Role (ROLE_ADMIN or ROLE_USER)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $role  = $input->getArgument('role');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return Command::FAILURE;
        }

        $user->setRoles([$role, 'ROLE_USER']);
        $this->em->flush();

        $output->writeln("<info>Role $role assigned to $email</info>");

        return Command::SUCCESS;
    }
}
