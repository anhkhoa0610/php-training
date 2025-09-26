<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        // Force id to integer to avoid injection and type confusion
        $id = (int) $id;

        $stmt = self::$_connection->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $user;
    }

    public function findUser($keyword) {
        // use wildcard in PHP, not in SQL string concatenation
        $like = '%' . $keyword . '%';

        $stmt = self::$_connection->prepare('SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?');
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $users;
    }
    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userName, $password) {
    // Tính md5 của mật khẩu nhập vào
    $md5Password = md5($password);

    // Dùng prepared statement để tránh SQL injection
    $stmt = self::$_connection->prepare('SELECT * FROM users WHERE name = ? AND password = ? LIMIT 1');
    $stmt->bind_param('ss', $userName, $md5Password);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    $stmt->close();

    return $user;
}

    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $id = (int) $id;

        $stmt = self::$_connection->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $ok ? $affected : false;
    }
    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input) {
        $id = (int) $input['id'];
        $name = $input['name'];

        if (!empty($input['password'])) {
            $hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $stmt = self::$_connection->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?');
            $stmt->bind_param('ssi', $name, $hash, $id);
        } else {
            $stmt = self::$_connection->prepare('UPDATE users SET name = ? WHERE id = ?');
            $stmt->bind_param('si', $name, $id);
        }

        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $ok ? $affected : false;
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input) {
        $name = $input['name'];
        $md5 = md5($input['password']);

        $stmt = self::$_connection->prepare('INSERT INTO users (name, password) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $md5);
        $ok = $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

        return $ok ? $insertId : false;
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        if (!empty($params['keyword'])) {
            $like = '%' . $params['keyword'] . '%';
            $stmt = self::$_connection->prepare('SELECT * FROM users WHERE name LIKE ?');
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $result = $stmt->get_result();
            $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        } else {
            // no params -> simple select
            $stmt = self::$_connection->prepare('SELECT * FROM users');
            $stmt->execute();
            $result = $stmt->get_result();
            $users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }

        return $users;
    }
}