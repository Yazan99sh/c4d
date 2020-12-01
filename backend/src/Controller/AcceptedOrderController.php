<?php

namespace App\Controller;

use App\AutoMapping;
use App\Service\AcceptedOrderService;
use App\Service\UserService;
use App\Request\AcceptedOrderCreateRequest;
use App\Request\AcceptedOrderUpdateRequest;
use App\Request\GetByIdRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use stdClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AcceptedOrderController extends BaseController
{
    private $autoMapping;
    private $validator;
    private $acceptedOrderService;
    private $userService;

    public function __construct(SerializerInterface $serializer, AutoMapping $autoMapping, ValidatorInterface $validator, AcceptedOrderService $acceptedOrderService, UserService $userService)
    {
        parent::__construct($serializer);
        $this->autoMapping = $autoMapping;
        $this->validator = $validator;
        $this->acceptedOrderService = $acceptedOrderService;
        $this->userService = $userService;
    }

    /**
     * @Route("/acceptedOrder",   name="createAcceptedOrder", methods={"POST"})
     * @IsGranted("ROLE_CAPTAIN")
     */
    public function create(Request $request)
    {   
        $response ="this user inactive!!";
        $status = $this->userService->userIsActive('captain', $this->getUserId());
        
        if ($status == 'active') {
            $data = json_decode($request->getContent(), true);

            $request = $this->autoMapping->map(stdClass::class, AcceptedOrderCreateRequest::class, (object)$data);

            $request->setCaptainID($this->getUserId());

            $violations = $this->validator->validate($request);
            if (\count($violations) > 0) {
                $violationsString = (string) $violations;

                return new JsonResponse($violationsString, Response::HTTP_OK);
            }

            $response = $this->acceptedOrderService->create($request);
        }

        return $this->response($response, self::CREATE);
    }

    /**
     * @Route("/acceptedOrder/{acceptedOrderId}", name="GetOrderStatusForCaptain", methods={"GET"})
     * @IsGranted("ROLE_CAPTAIN")
     * @param                                     Request $request
     * @return                                    JsonResponse
     */
    public function acceptedOrder($acceptedOrderId)
    {
        $result = $this->acceptedOrderService->acceptedOrder($this->getUserId(), $acceptedOrderId);

        return $this->response($result, self::FETCH);
    }

    /**
     * @Route("/totalEarn",       name="GetTotalEarnForCaptain", methods={"GET"})
     * @IsGranted("ROLE_CAPTAIN")
     * @param                     Request $request
     * @return                    JsonResponse
     */
    public function totalEarn()
    {
        $result = $this->acceptedOrderService->totalEarn($this->getUserId());

        return $this->response($result, self::FETCH);
    }

    /**
     * @Route("acceptedOrder", name="updateAcceptedOrder", methods={"PUT"})
     * @IsGranted("ROLE_CAPTAIN")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $request = $this->autoMapping->map(\stdClass::class, AcceptedOrderUpdateRequest::class, (object) $data);

        $violations = $this->validator->validate($request);

        if (\count($violations) > 0) {
            $violationsString = (string) $violations;

            return new JsonResponse($violationsString, Response::HTTP_OK);
        }

        $result = $this->acceptedOrderService->update($request);

        return $this->response($result, self::UPDATE);
    }
}
