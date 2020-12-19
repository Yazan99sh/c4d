<?php

namespace App\Service;

use App\AutoMapping;
use App\Entity\SubscriptionEntity;
use App\Manager\SubscriptionManager;
use App\Request\SubscriptionCreateRequest;
use App\Response\SubscriptionResponse;
use App\Response\SubscriptionByIdResponse;
use SebastianBergmann\Comparator\DateTimeComparator;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class SubscriptionService
{
    private $autoMapping;
    private $subscriptionManager;

    public function __construct(AutoMapping $autoMapping, SubscriptionManager $subscriptionManager)
    {
        $this->autoMapping = $autoMapping;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function create(SubscriptionCreateRequest $request)
    {
        $subscriptionResult = $this->subscriptionManager->create($request);

        return $this->autoMapping->map(SubscriptionEntity::class, SubscriptionResponse::class, $subscriptionResult);
    }

    public function activeSubscription($userId)
    {
        return $this->subscriptionManager->activeSubscription($userId);
    }

    public function update($request)
    {
        $result = $this->subscriptionManager->update($request);

        return $this->autoMapping->map(SubscriptionEntity::class, SubscriptionResponse::class, $result);
    }

    public function updateFinishe($id)
    {
        $result = $this->subscriptionManager->updateFinishe($id);

        return $this->autoMapping->map(SubscriptionEntity::class, SubscriptionResponse::class, $result);
    }

    public function getSubscriptionsPending()
    {
        $response = [];
        $items = $this->subscriptionManager->getSubscriptionsPending();
       
        foreach ($items as $item) {
            $response[] = $this->autoMapping->map('array', SubscriptionByIdResponse::class, $item);
        }
        return $response;
    }
    
    public function getSubscriptionById($id)
    {
        $response = [];
        $items = $this->subscriptionManager->getSubscriptionById($id);
      
        foreach ($items as $item) {
            $response[] = $this->autoMapping->map('array', SubscriptionByIdResponse::class, $item);
        }
        return $response;
    }

    public function subscriptionIsActive($ownerID)
    {
        $item = $this->subscriptionManager->subscriptionIsActive($ownerID);
        if ($item) {
          return  $item[0]['status'];
        }

        return $item ;
     }

    // check subscription , if time is finishe or order count is finishe, change status value to 'finished'
    
    public function saveFinisheAuto($ownerID)
    {
        $remainingOrdersOfPackage = $this->subscriptionManager->getRemainingOrders($ownerID);
       // get subscripe start date after month
        $startDate1 =date_timestamp_get($remainingOrdersOfPackage['startDate']);
        
        $startDate2 = (json_decode($startDate1));

        $startDate = date("Y-m-d h:i:s", $startDate2);
        
        $startDateNextMonth = date('Y-m-d h:i:s', strtotime("1 month",strtotime($startDate)));
        // dd($startDate , $startDateNextMonth);

       // get subscripe end date format normal
        $endDate1 =date_timestamp_get($remainingOrdersOfPackage['endDate']);
        
        $endDate2 = (json_decode($endDate1));

        $endDate = date("Y-m-d h:i:s", $endDate2);

        if ($remainingOrdersOfPackage['remainingOrders'] == 0)  {
      
            $this->updateFinishe($remainingOrdersOfPackage['id']);
            $response[] = ["subscripe finished, count Orders finished"];
        }

        if ($startDateNextMonth == $endDate)  {
      
            $this->updateFinishe($remainingOrdersOfPackage['id']);
            $response[] = ["subscripe finished, finished date"];
        }
        
        $response[] = $remainingOrdersOfPackage;
        
        return $response;
     }

     public function dashboardContracts()
    {
        $response = [];

        $response[] = $this->subscriptionManager->countpendingContracts();
        $response[] = $this->subscriptionManager->countDoneContracts();
        $response[] = $this->subscriptionManager->countCancelledContracts();

        $subscriptionsPending = $this->subscriptionManager->getSubscriptionsPending();
       
        foreach ($subscriptionsPending as $item) {
            $response[] = $this->autoMapping->map('array', SubscriptionByIdResponse::class, $item);
        }
        
        return $response;
    }
}
