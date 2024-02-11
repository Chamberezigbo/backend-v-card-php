<?php

class DataClass{
       public function getExistingLogoPath($userId){
              $query = "SELECT logo FROM business_details WHERE user_id = :user_id";
              $params = ['user_id' => $userId];
              $result = $this->SelectOne($query, $params);

              return $result ? $result['logo'] : null;
       }
}