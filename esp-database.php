<?php
    $servername = "localhost";
    $dbname = "esp_data";
    $username = "root";
    $password = "";

    function createOutput($name, $board, $gpio, $state, $type) {
        global $servername, $username, $password, $dbname;
    
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        $sql = "INSERT INTO Outputs (name, board, gpio, state, type)
                VALUES ('$name', '$board', '$gpio', '$state', '$type')";
    
        if ($conn->query($sql)) { 
            $conn->close();
            return "New GPIO created successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
            $conn->close();
            return $error;
        }
    }
    

    function updateInput($board, $gpio, $state) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE Outputs SET state='$state' 
                WHERE board='$board' AND gpio='$gpio' AND type='input'";

        if ($conn->query($sql)) {
            return "Input state updated";
        } else {
            return "Error updating input: " . $conn->error;
        }
        $conn->close();
    }

    function deleteOutput($id) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "DELETE FROM Outputs WHERE id='$id'";
        $conn->query($sql);
        $conn->close();
        return "Output deleted";
    }

    function updateOutput($id, $state) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE Outputs SET state='$state' WHERE id='$id'";
        $conn->query($sql);
        $conn->close();
        return "Output updated";
    }

    function getAllOutputs() {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT id, name, board, gpio, state, type FROM Outputs ORDER BY board";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    function getAllOutputStates($board) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT gpio, state, type FROM Outputs WHERE board='$board'";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    function getOutputBoardById($id) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT board FROM Outputs WHERE id='$id'";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    function updateLastBoardTime($board) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "UPDATE Boards SET last_request=NOW() WHERE board='$board'";
        $conn->query($sql);
        $conn->close();
    }

    function getAllBoards() {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT board, last_request FROM Boards ORDER BY board";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    function getBoard($board) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "SELECT board FROM Boards WHERE board='$board'";
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }

    function createBoard($board) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "INSERT INTO Boards (board) VALUES ('$board')";
        $conn->query($sql);
        $conn->close();
    }

    function deleteBoard($board) {
        global $servername, $username, $password, $dbname;

        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "DELETE FROM Boards WHERE board='$board'";
        $conn->query($sql);
        $conn->close();
    }
?>