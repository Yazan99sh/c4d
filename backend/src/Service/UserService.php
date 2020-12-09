<?php

namespace App\Service;

use App\AutoMapping;
use App\Entity\UserEntity;
use App\Entity\UserProfileEntity;
use App\Entity\CaptainProfileEntity;
use App\Manager\UserManager;
use App\Request\UserProfileCreateRequest;
use App\Request\UserProfileUpdateRequest;
use App\Request\CaptainProfileCreateRequest;
use App\Request\CaptainProfileUpdateRequest;
use App\Request\UserRegisterRequest;
use App\Response\UserProfileCreateResponse;
use App\Response\CaptainProfileCreateResponse;
use App\Response\UserProfileResponse;
use App\Response\UserRegisterResponse;
use App\Response\RemainingOrdersResponse;
use App\Response\CaptainsOngoingResponse;
use App\Response\CaptainTotalBounceResponse;


class UserService
{
    private $autoMapping;
    private $userManager;
    private $acceptedOrderService;
    private $ratingService;

    public function __construct(AutoMapping $autoMapping, UserManager $userManager, AcceptedOrderService $acceptedOrderService, RatingService $ratingService)
    {
        $this->autoMapping = $autoMapping;
        $this->userManager = $userManager;
        $this->acceptedOrderService = $acceptedOrderService;
        $this->ratingService = $ratingService;
    }

    public function userRegister(UserRegisterRequest $request)
    {
        $userRegister = $this->userManager->userRegister($request);

        return $this->autoMapping->map(UserEntity::class, UserRegisterResponse::class, $userRegister);
    }

    public function userProfileCreate(UserProfileCreateRequest $request)
    {
        $userProfile = $this->userManager->userProfileCreate($request);

        if ($userProfile instanceof UserProfile) {

            return $this->autoMapping->map(UserProfileEntity::class,UserProfileCreateResponse::class, $userProfile);
       }
        if ($userProfile == true) {
          
           return $this->getUserProfileByUserID($request->getUserID());
       }
    }

    public function userProfileUpdate(UserProfileUpdateRequest $request)
    {
        $item = $this->userManager->userProfileUpdate($request);

        return $this->autoMapping->map(UserProfileEntity::class, UserProfileResponse::class, $item);
    }

    public function getUserProfileByUserID($userID)
    {
        $item = $this->userManager->getUserProfileByUserID($userID);

        return $this->autoMapping->map('array', UserProfileCreateResponse::class, $item);
    }

    public function getremainingOrders($userID)
    {
        $respons = [];
        $items = $this->userManager->getremainingOrders($userID);

        foreach ($items as $item) {
            $respons = $this->autoMapping->map('array', RemainingOrdersResponse::class, $item);
        }
        return $respons;
    }

    public function captainprofileCreate(CaptainProfileCreateRequest $request)
    {
        $captainProfile = $this->userManager->captainprofileCreate($request);
       
        if ($captainProfile instanceof captainProfile) {
            return $this->autoMapping->map(CaptainProfileEntity::class, CaptainProfileCreateResponse::class, $captainProfile);
        }
        if ($captainProfile == true) {
           
            return $this->getcaptainprofileByCaptainID($request->getCaptainID());
        }
       
    }

    public function captainprofileUpdate(CaptainProfileUpdateRequest $request)
    {
        $item = $this->userManager->captainprofileUpdate($request);

        return $this->autoMapping->map(CaptainProfileEntity::class, CaptainProfileCreateResponse::class, $item);
    }

    public function getcaptainprofileByCaptainID($captainID)
    {
        $response=[];

        $item = $this->userManager->getcaptainprofileByCaptainID($captainID);

        $bounce = $this->totalBounceCaptain($item['id']);

        $countOrdersDeliverd = $this->acceptedOrderService->countAcceptedOrder($captainID);

        $item['rating'] = $this->ratingService->getRatingByCaptainID($captainID);
        $response = $this->autoMapping->map('array', CaptainProfileCreateResponse::class, $item);
      
        $response->bounce = $bounce;
        $response->countOrdersDeliverd = $countOrdersDeliverd;

        return $response;
    }

    public function getCaptainprofileByID($captainProfileId)
    {
        $response=[];
        $item = $this->userManager->getCaptainprofileByID($captainProfileId);
   
        $bounce = $this->totalBounceCaptain($item['id']);
        
        $countOrdersDeliverd = $this->acceptedOrderService->countAcceptedOrder($item['captainID']);

        $item['rating'] = $this->ratingService->getRatingByCaptainID($item['captainID']);
    
        $response =  $this->autoMapping->map('array', CaptainProfileCreateResponse::class, $item);

        $response->bounce = $bounce;
        $response->countOrdersDeliverd = $countOrdersDeliverd;
      
        return $response;
    }

    public function getUserInactive($userType)
    {
        $response = [];
        $items = $this->userManager->getUserInactive($userType);

        if($userType == "captain") {
            foreach( $items as  $item ) {
                $response  = $this->autoMapping->map('array', CaptainProfileEntity::class, $item);
            }
        }
        if($userType == "owner") {
            foreach( $items as  $item ) {
                $response  = $this->autoMapping->map('array', UserProfileResponse::class, $item);
            }
        }
     return $response;
    }
    public function getCaptainsState($state)
    {
        $response = [];
        $items = $this->userManager->getCaptainsState($state);

        foreach( $items as  $item ) {
           
            $item['bounce'] = $this->totalBounceCaptain($item['id']);
           
            $item['countOrdersDeliverd'] = $this->acceptedOrderService->countAcceptedOrder($item['captainID']);
           
            $item['rating'] = $this->ratingService->getRatingByCaptainID($item['captainID']);
            
            $response[]  = $this->autoMapping->map('array', CaptainProfileCreateResponse::class, $item);
            }
        return $response;
    }

    public function captainIsActive($captainID)
    {
        $item = $this->userManager->captainIsActive($captainID);
        if ($item) {
          return  $item[0]['status'];
        }

        return $item ;
     }

    
     public function dashboardCaptains()
     {
         $response = [];

         $response[] = $this->userManager->countpendingCaptains();
         $response[] = $this->userManager->countOngoingCaptains();
         $response[] = $this->userManager->countDayOfCaptains();

         return $response;
     }

     public function totalBounceCaptain($captainProfileId)
    {
        $response = [];
        $item = $this->userManager->totalBounceCaptain($captainProfileId);
        //إذا أردنا الإعتماد على حقل ستيت في جدول الأكسبت أوردر
        // $item['countOrdersDeliverd'] = $this->acceptedOrderService->countOrdersDeliverd($item[0]['captainID']);
        if ($item) {
             $countAcceptedOrder = $this->acceptedOrderService->countAcceptedOrder($item[0]['captainID']);

             $item['bounce'] = $item[0]['bounce'] * $countAcceptedOrder[0]['countOrdersDeliverd'];

             $response  = $this->autoMapping->map('array', CaptainTotalBounceResponse::class,  $item);
        }
        return $response;
    }
 
}
