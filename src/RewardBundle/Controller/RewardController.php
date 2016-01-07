<?php

namespace RewardBundle\Controller;

use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use RewardBundle\Repository\RewardRepository;
use RewardBundle\Entity\Reward;
use RewardBundle\Services\ProductCategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class RewardController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        /** @var RewardRepository $rewardRepository */
        $rewardRepository = $this->get('reward.reward_repository');

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');

        $mixpanel->addEvent(new Event('Reward List'));

        /** @var ProductCategoryService $productCategoryService */
        $productCategoryService = $this->get('reward.product_category_service');

        $categories = $productCategoryService->getCategories();

        $rewards = $rewardRepository->findRewardsForUser($this->getUser());

        return $this->render('RewardBundle:Reward:list.html.twig',
            array('rewards' => $rewards, 'product_cats' => $categories)
        );
    }

    public function newAction(Request $request)
    {
        $reward = new Reward();

        $reward->setUser($this->getUser());

        $form = $this->createFormBuilder($reward)
            ->add('title', TextType::class)
            ->add('date', DateType::class, array(
                'data' => new \DateTime(),
            ))
            ->add('cost', MoneyType::class, array(
                'scale' => 2,
                'currency' => $this->getUser()->getCurrency(),
                'constraints' => new GreaterThanOrEqual(array(
                    'value' => 0,
                    'message' => 'Your savings should be a positive number',
                )), ))
            ->add('submit', SubmitType::class, array(
                'attr' => array(
                    'class' => "btn btn-success"
                )
            ))
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RewardRepository $rewardRepository */
            $rewardRepository = $this->get('reward.reward_repository');

            $rewardRepository->save($reward);

            $this->get('mixpanel')->addEvent(new Event('Add Reward'));

            return $this->redirectToRoute('reward.reward.list');
        }

        return $this->render('RewardBundle:Reward:new.html.twig', array('form' => $form->createView()));
    }

    public function editAction(Request $request, int $id)
    {
        /** @var RewardRepository $rewardRepository */
        $rewardRepository = $this->get('reward.reward_repository');

        $reward = $rewardRepository->find($id);

        if (!$reward instanceof Reward || $this->getUser() !== $reward->getUser()) {
            throw new NotFoundHttpException();
        }

        $form = $this->createFormBuilder($reward)
            ->add('title', TextType::class)
            ->add('date', DateType::class, array(
                'data' => new \DateTime(),
            ))
            ->add('cost', MoneyType::class, array(
                'scale' => 2,
                'currency' => $this->getUser()->getCurrency(),
                'constraints' => new GreaterThanOrEqual(array(
                    'value' => 0,
                    'message' => 'Your savings should be a positive number',
                )), ))
            ->add('submit', SubmitType::class, array(
                'attr' => array(
                    'class' => "btn btn-success"
                )
            ))
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $rewardRepository->save($reward);

            return $this->redirectToRoute('reward.reward.list');
        }

        return $this->render('RewardBundle:Reward:edit.html.twig', array(
            'form' => $form->createView(),
            'reward' => $reward
        ));
    }

    /***
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, int $id) : RedirectResponse {

        /** @var RewardRepository $rewardRepository */
        $rewardRepository = $this->get('reward.reward_repository');

        $reward = $rewardRepository->find($id);

        if (!$reward instanceof Reward || $reward->getUser() !== $this->getUser())
        {
            throw new NotFoundHttpException();
        }

        // This user owns the reward, so let's delete

        $this->get('mixpanel')->addEvent(new Event('Delete Reward', $reward->serialize()));

        $rewardRepository->remove($reward);

        return $this->redirectToRoute('reward.reward.list');
    }
}
