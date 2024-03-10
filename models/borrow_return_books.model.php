<?php
   class BorrowReturnBooks{
   private $conn;
   public  $id;
   public $user_id;
   public $book_id;
   public $borrowed_day;
   public $returned_day;
   
   public function __construct($db){
    $this->conn=$db;
   }
   
   public function getBorrowReturnBooks(){
   $query_params =[];
   $query = 
   " SELECT borrow_return_books.*, users.id as user_id,books.id as book_id
   FROM borrow_return_book
   INNER JOIN users ON borrow_return_book.user_id = users.id
   INNER JOIN books ON borrow_return_book.book_id = books.id
   WHERE 1=1";
     if($this->user_id){
        $query = $query."AND user_id = ?";
        array_push($query_params,$this->user_id);
    }
    if($this->book_id){
        $query = $query."AND book_id = ?";
        array_push($query_params,$this->book_id);
    }
    $offset = ($_GET['page'] - 1) *$_GET['limit'];
    
    $query= $query."LIMIT ". $_GET['limit'] . "OFFSET ". $offset." ";
    $stmt = $this->conn->prepare($query);
    for ($i = 0; $i < count($query_params); $i++) {
        $stmt->bindParam($i + 1, $query_params[$i]);
    }
    $stmt->execute();
    $num = $stmt->rowCount();
    $query2 =  "SELECT COUNT(*) as total_rows FROM BORROW_RETURN_BOOKS";
    $stmt2 = $this->conn->prepare($query2);
    $stmt2->execute();
    $total_rows_result = $stmt2->fetch(PDO::FETCH_ASSOC);
    $total_rows = $total_rows_result['total_rows'];
    $total_pages = ceil($total_rows / $_GET['limit']);
    $this-> conn = null;
    if($num>0){
    $results_array= [];
    while($row= $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
             $item = array(
                        'id'=> $id,
                        'user_id'=>$user_id,
                        'book_id'=>$book_id,
                        'borrowed_day'=>$borrowed_day,
                        'returned_day'=>$returned_day,
                    );
                    array_push($results_array,$item);
                }
                return (array("message"=>"Successfully",'data'=>$results_array, 'total_page' => $total_pages));
            }
            else{
               return (array('message:'=>"không tìm thấy sách"));    
            }
            return $stmt;
        }


public function createBorrowReturnBooks(){
   $query ="INSERT INTO borrow_return_books( user_id, book_id, borrowed_day,returned_day) VALUES(:user_id, :book_id, NOW(), :returned_day)";
   $stmt = $this->conn->prepare($query);
   $stmt->bindParam(':user_id', $this->user_id);
   $stmt->bindParam(':book_id', $this->book_id);
   $stmt->bindParam(':returned_day', null,PDO::PARAM_NULL);
   $stmt->execute();
   $this->conn = null;
   return array("message"=>"tạo sách mượn công thành công.");
}
public function deleteBorrowReturnBooks(){
   $query="DELETE FROM borrow_return_books where id=:id ";
   $stmt = $this->conn->prepare($query);
   $stmt->bindParam(':id', $this->id);
   $stmt->execute();
   $affectedRows = $stmt->rowCount();
   $this->conn = null;
   if($affectedRows >0){
       return array("message"=>"xóa sách thành công.");
   }else{
       http_response_code(404);
       return array("errors"=>"xóa sách thất bại, book not found");

   }
}

public function updateBorrowReturnBooks(){
    $query = "UPDATE borrow_return_books SET returned_day = CURRENT_TIMESTAMP() where id =:id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $this->id);
    $stmt->execute();
    $this->conn = null;
    $affectedRows = $stmt->rowCount();
    $this->conn = null;
    if($affectedRows >0){
        return array("message"=>"cập nhật sách mượn thành công");
    }else{
        http_response_code(404);
        return array("errors"=>"cập nhật sách thất bại, book not found");

    }
}

}
?>
