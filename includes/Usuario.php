<?php

class Usuario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarPorEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM USUARIO WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function existeNickOEmail($nick, $email) {
        $stmt = $this->db->prepare('SELECT id FROM USUARIO WHERE nick = ? OR email = ?');
        $stmt->execute([$nick, $email]);
        return $stmt->fetch() !== false;
    }

    public function registrar($nombre, $nick, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO USUARIO (nombre, nick, email, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nombre, $nick, $email, $hash]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare('SELECT * FROM USUARIO WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
