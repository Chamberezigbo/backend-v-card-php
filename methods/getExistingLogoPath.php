<?php
class DataClass
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getExistingLogoPath($userId)
    {
        $query = "SELECT logo FROM business_details WHERE user_id = :user_id";
        $params = ['user_id' => $userId];
        $result = $this->db->SelectOne($query, $params);

        return $result ? $result['logo'] : null;
    }
}