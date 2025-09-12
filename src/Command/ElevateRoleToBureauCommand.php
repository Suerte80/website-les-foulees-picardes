<?php

namespace App\Command;

use App\Repository\MemberRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Utilisation de la commande:
 * symfony console app:user:elevate:bureau role
 *
 * ATTENTION: ça override les rôles !
 *
 * exemple:
 * Augmente le rôle d'un utilisateur vers le rôle de secretaire.
 *
 * symfony console app:user:elevate:bureau secretaire
 */

#[AsCommand(
    name: 'app:user:elevate:bureau',
    description: 'Add a short description for your command',
)]
class ElevateRoleToBureauCommand extends Command
{
    private MemberRepository $memberRepository;
    private RoleRepository $roleRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MemberRepository $memberRepository, RoleRepository $roleRepository, EntityManagerInterface $entityManager)
    {
        $this->memberRepository = $memberRepository;

        $this->roleRepository = $roleRepository;

        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Rôle Suplémentaire')
            ->addArgument('arg2', InputArgument::OPTIONAL, 'Email de l\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $arg2 = $input->getArgument('arg2');

        // Récupération des arguments
        $roleArg = $arg1;
        if (!$arg1) {
            $roleQ = new Question('Tapez le rôle de l\'utilisateur ? ');
            $roleArg = $io->askQuestion($roleQ);
        }

        $email = $arg2;
        if(!$arg2){
            $emailQ = new Question('Tapez l\'email de l\'utilisateur ? ');
            $email = $io->askQuestion($emailQ);
        }

        // Vérification des arguments
        $member = $this->memberRepository->findOneBy(['email' => $email]);
        if(!$member){
            $io->error('L\'utilisateur '.$email.' n\'existe pas');
            return Command::FAILURE;
        }

        $role = $this->roleRepository->findOneBy(['roleName' => $roleArg]);
        if(!$role){
            $io->error('Le rôle ' . $roleArg . ' n\'existe pas');
            return Command::FAILURE;
        }

        // Récupération du role bureau
        $roleBureau = $this->roleRepository->findOneBy(['code' => 'ROLE_BUREAU']);
        if(!$roleBureau){
            $io->error('Erreur interne.');
            return Command::FAILURE;
        }

        // Vérification que le membre est actif.
        if(!$member->isActive()){
            $io->error('Vous devez choisir un membre actif.');
            return Command::FAILURE;
        }

        // Injection des rôles dans le membre.
        $member->setRoles([$roleBureau, $role]);

        $this->entityManager->flush();

        $io->success('Le rôle de l\'utilisateur a été élevé.');
        return Command::SUCCESS;
    }
}
