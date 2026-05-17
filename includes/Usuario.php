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

    public function existeNick($nick) {
        $stmt = $this->db->prepare('SELECT id FROM USUARIO WHERE nick = ?');
        $stmt->execute([$nick]);
        return $stmt->fetch() !== false;
    }

    public function existeEmail($email) {
        $stmt = $this->db->prepare('SELECT id FROM USUARIO WHERE email = ?');
        $stmt->execute([$email]);
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

    public function obtenerPorNick($nick) {
        $stmt = $this->db->prepare('SELECT * FROM USUARIO WHERE nick = ?');
        $stmt->execute([$nick]);
        return $stmt->fetch();
    }

    public function existeNickDeOtroUsuario($idUsuario, $nick) {
        $stmt = $this->db->prepare('SELECT id FROM USUARIO WHERE nick = ? AND id <> ?');
        $stmt->execute([$nick, $idUsuario]);
        return $stmt->fetch() !== false;
    }

    public function actualizarPerfil($idUsuario, $nombre, $nick, $biografia, $avatar, $encabezado) {
        $stmt = $this->db->prepare('UPDATE USUARIO
                                    SET nombre = ?, nick = ?, biografia = ?, avatar = ?, encabezado = ?
                                    WHERE id = ?');

        return $stmt->execute([$nombre, $nick, $biografia, $avatar, $encabezado, $idUsuario]);
    }

    public function actualizarPassword($idUsuario, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE USUARIO SET password = ? WHERE id = ?');

        return $stmt->execute([$hash, $idUsuario]);
    }
}
