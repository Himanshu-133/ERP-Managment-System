<?php
require_once 'database.php';

class Student {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getStudentDetails($user_id) {
        $query = "SELECT * FROM student_details WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateStudentDetails($user_id, $data) {
        $existing = $this->getStudentDetails($user_id);
        
        if ($existing) {
            $query = "UPDATE student_details SET name = :name, roll_number = :roll_number, 
                     email = :email, department = :department, course = :course, 
                     phone = :phone, address = :address WHERE user_id = :user_id";
        } else {
            $query = "INSERT INTO student_details (user_id, name, roll_number, email, 
                     department, course, phone, address) VALUES (:user_id, :name, 
                     :roll_number, :email, :department, :course, :phone, :address)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':roll_number', $data['roll_number']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':course', $data['course']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        
        return $stmt->execute();
    }
    
    public function getAllStudents() {
        $query = "SELECT sd.*, u.username FROM student_details sd 
                 JOIN users u ON sd.user_id = u.id ORDER BY sd.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStudentById($id, $data) {
        $query = "UPDATE student_details SET name = :name, roll_number = :roll_number, 
                 email = :email, department = :department, course = :course, 
                 phone = :phone, address = :address WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':roll_number', $data['roll_number']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':course', $data['course']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        
        return $stmt->execute();
    }

    public function createStudent($user_id, $data) {
        // Insert a new student_details row associated with a user_id
        $query = "INSERT INTO student_details (user_id, name, roll_number, email, department, course, phone, address) VALUES (:user_id, :name, :roll_number, :email, :department, :course, :phone, :address)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':roll_number', $data['roll_number']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':course', $data['course']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);

        return $stmt->execute();
    }

    public function deleteStudentById($id) {
        // Delete student_details row and corresponding user row in a transaction
        try {
            $this->conn->beginTransaction();

            // Find the user_id for this student_details row
            $q = "SELECT user_id FROM student_details WHERE id = :id";
            $s = $this->conn->prepare($q);
            $s->bindParam(':id', $id);
            $s->execute();
            $row = $s->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                // nothing to delete
                $this->conn->rollBack();
                return false;
            }

            $user_id = (int)$row['user_id'];

            // Delete student_details
            $del1 = $this->conn->prepare('DELETE FROM student_details WHERE id = :id');
            $del1->bindParam(':id', $id);
            $ok1 = $del1->execute();

            // Delete users row
            $del2 = $this->conn->prepare('DELETE FROM users WHERE id = :uid');
            $del2->bindParam(':uid', $user_id);
            $ok2 = $del2->execute();

            if ($ok1 && $ok2) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }
}
?>
