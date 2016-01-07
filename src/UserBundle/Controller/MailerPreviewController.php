<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Services\MailerGeneratorService;

class MailerPreviewController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function monthlyPreviewAction(Request $request)
    {
        /** @var MailerGeneratorService $mailerGenerator */
        $mailerGenerator = $this->get('user_bundle.mailer_generator');

        return $this->render('UserBundle:Mailer:monthly.html.twig',
            $mailerGenerator->getMonthlyEmail($this->getUser(), (new \DateTime('first day of last month'))->setTime(0,0,0), (new \DateTime('last day of last month'))->setTime(23,59,59),
                (new \DateTime('-2 months'))->setTime(0,0,0)->setDate(2016, null, 1)
                )
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rewardsIntroAction(Request $request)
    {
        /** @var MailerGeneratorService $mailerGenerator */
        $mailerGenerator = $this->get('user_bundle.mailer_generator');

        return $this->render('UserBundle:Mailer:rewards_intro.html.twig',
            $mailerGenerator->getRewardsIntroEmail($this->getUser())
        );
    }
}
