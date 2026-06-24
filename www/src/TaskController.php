<?php

class TaskController 
{
    public function processRequest(string $method, string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                echo "index";
            } elseif ($method == "POST") {
                echo "create";
            } else {
                $this->respondMethodNotAllowed("GET", "POST");
            }
        } else {
            
        }
    }
}