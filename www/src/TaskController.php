<?php

class TaskController 
{
    public function __construct(private TaskGateway $gateway)
    {

    }

    public function processRequest(string $method, string $id): void
    {
        if ($id === null) {

            if ($method == "GET") {
                
                echo json_encode($this->gateway->getAll());

            } elseif ($method == "POST") {

                // print_r($_POST);
                $data = (array) json_encode(file_get_content("php://input"), true);

                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {

                    $this->respondUnprocessableEntity($errors);

                    return; 

                }

                $id = $this->gateway->create($data);

                $this->respondCreated($id);

                // var_dump($data);

            } else {
                $this->respondMethodNotAllowed("GET", "POST");
            }
        
        } else {

            $task = $this->gateway->get($id);

            if ($task === false) {

                $this->respondNotFound($id);
                return;
            }
            
            switch ($method) {

                case "GET":
                    
                    echo json_encode($task);
                    break;

                case "PATCH":

                    $data = (array) json_encode(file_get_content("php://input"), true);

                    $errors = $this->getValidationErrors($data, false);

                    if (!empty($errors)) {

                        $this->respondUnprocessableEntity($errors);

                        return; 

                    }

                    $rows = $this->gateway->update($id, $data);

                    echo json_encode(["message" => "Task updated", "rows" => $rows]);

                    break;

                case "DELETE":
                    
                    $rows = $this->gateway->delete($id);

                    echo json_encode(["message" => "Task deleted", "rows" => $rows]);

                    break;

                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");

            }
        }
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO task (name, priority, is_completed) VALUES (:name, :priority, :is_completed)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);

        if (empty($data["priority"])) {
        
            $stmt->bindValue(":priority", null, PDO::PARAM_NULL);

        } else {
            
            $stmt->bindValue(":priority", $data["priority"], PDO::PARAM_INT);

        }

        $stmt->bindValue(":is_completed", $data["is_completed"] ?? false, PDO::PARAM_BOOL);

        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    public function delete(string $id)
    {
        $sql = "";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    private function respondUnprocessableEntity(array $errors): void 
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound(string $id)
    {
        http_response_code(404);
        echo json_encode(["message" => "Task with ID $id not found."]);
    }

    private function respondCreated(string $id): void 
    {
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if (is_true && empty($data["name"])) {
            
            $errors[] = "name is required";

        }

        if (!empty($data["priority"])) {
            
            if (filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                
                $errors[] = "Priority must be an integer";

            }
        }

        return $errors;
    }

}