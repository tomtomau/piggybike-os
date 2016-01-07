<?php

namespace StravaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function handleAction(Request $request)
    {
        $params = $this->digestJSONRequest($request);

        $webhookService = $this->get('strava.webhook_service');

        return $webhookService->handleWebhook($params);
    }

    /**
     * Respond to hub challenge.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws InternalErrorException
     */
    public function challengeAction(Request $request)
    {
        $hubChallenge = $request->get('hub_challenge', null);

        if (is_string($hubChallenge)) {
            return new JsonResponse(array(
                'hub.challenge' => $hubChallenge,
            ));
        }

        throw new InternalErrorException();
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function digestJSONRequest(Request $request)
    {
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        return $params;
    }
}
