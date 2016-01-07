<?php

namespace ActivityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;
use UserBundle\Services\MailerGeneratorService;

/**
 * Send monthly email
 */
class SendEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('send:email')
            ->setDescription('Send an email')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Whether to actually send the email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var UserRepository $userRepo */
        $userRepo = $container->get('user_bundle.user_repository');

        $startDate = new \DateTime("midnight first day of last month");
        $endDate = new \DateTime("midnight first day of this month");
        $lastStartDate = new \DateTime("midnight first day of 2 months ago");

        /** @var User[] $users */
        $users = $userRepo->findUsersForMonthlyEmail($startDate, $endDate);

        /** @var MailerGeneratorService $mailerGenerator */
        $mailerGenerator = $container->get('user_bundle.mailer_generator');

        /** @var TwigEngine $templating */
        $templating = $container->get('templating');

        foreach ($users as $user) {
            if ($input->getOption('force')) {
                $output->writeln(sprintf('Mailing %s', $user->getEmail()));

                $params = $mailerGenerator->getMonthlyEmail($user, $startDate, $endDate, $lastStartDate);

                $html = $templating->render('UserBundle:Mailer:monthly.html.twig', $params);

                $message = \Swift_Message::newInstance()
                    ->setSubject($params['subject'])
                    ->setFrom('tom@piggy.bike', 'Tom @ PiggyBike')
                    ->setTo($user->getEmail())
                    ->setBody($html, 'text/html');

                $this->getContainer()->get('mailer')->send($message);
            } else {
                $output->writeln(sprintf('Would mail %s', $user->getEmail()));
            }
        }
    }
}
