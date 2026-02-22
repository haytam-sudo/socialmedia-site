<?php
include_once "/opt/lampp/htdocs/website/config/config.php";

class Profile
{
    private $id;
    private $username;
    private $email;
    private $password_hash;
    private $bio;
    private $dateofbirth;
    private $avatar_url;

    public function __construct(
        $username,
        $email,
        $password_hash,
        $dateofbirth,
        $bio = null,
        $avatar_url = null,
        $id = null
    ) {
        $this->id            = $id;
        $this->username      = $username;
        $this->email         = $email;
        $this->password_hash = $password_hash;
        $this->dateofbirth   = $dateofbirth;
        $this->bio           = $bio;
        $this->avatar_url    = $avatar_url;
    }

    public function addprofile()
    {
        try {
            global $pdo;

            $sql = "
                INSERT INTO profiles (
                    username,
                    email,
                    password_hash,
                    bio,
                    avatar_url,
                    date_of_birth
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $this->username,
                $this->email,
                $this->password_hash,
                $this->bio,
                $this->avatar_url,
                $this->dateofbirth
            ]);

            $this->id = (int)$pdo->lastInsertId();
            return $this->id;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public static function getById($id)
    {
        try {
            global $pdo;

            $sql = "SELECT * FROM profiles WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return new Profile(
                $row['username'],
                $row['email'],
                $row['password_hash'],
                $row['date_of_birth'],
                $row['bio'],
                $row['avatar_url'],
                $row['id']
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    public function editprofile($username, $bio, $email, $avatarUrl,  $password, $profile_id)
    {
        try {
            global $pdo;

            if ($password !== "") {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "UPDATE profiles
                     SET username = ?, bio = ?, email = ?, avatar_url = ?, password_hash = ?
                     WHERE id = ?"
                );
                $stmt->execute([$username, $bio, $email, $avatarUrl, $password_hash, (int)$profile_id]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE profiles
                     SET username = ?, bio = ?, email = ?, avatar_url = ?
                     WHERE id = ?"
                );
                $stmt->execute([$username, $bio, $email, $avatarUrl, (int)$profile_id]);
            }

            if (function_exists('url')) {
                header('Location: ' . url('/profile/' . (int)$profile_id));
            } else {
                header("Location: /website/app/view/profileView.php?id=" . (int)$profile_id);
            }
            exit;
        } catch (PDOException $e) {
            error_log("EDIT PROFILE UPDATE ERROR: " . $e->getMessage());
            $msg = "Database error while saving changes.";
        }
    }
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getAvatarUrl()
    {
        return $this->avatar_url;
    }
    public function getBio()
    {
        return $this->bio;
    }
    public function getEmail()
    {
        return $this->email;
    }
}
