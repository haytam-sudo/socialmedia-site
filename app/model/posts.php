<?php
require_once "/opt/lampp/htdocs/website/config/config.php";

class Posts
{
    private $id;
    private $profile_id;
    private $title;
    private $content;
    private $img_url;
    private $created_at;
    private $updated_at;

    public function __construct(
        $profile_id,
        $title,
        $content,
        $img_url = null,
        $id = null,
        $created_at = null,
        $updated_at = null
    ) {
        $this->id         = $id;
        $this->profile_id = $profile_id;
        $this->title      = $title;
        $this->content    = $content;
        $this->img_url    = $img_url;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function addpost()
    {
        try {
            global $pdo;

            $sql = "
                INSERT INTO posts (
                    profile_id,
                    title,
                    content,
                    img_url
                )
                VALUES (?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $this->profile_id,
                $this->title,
                $this->content,
                $this->img_url
            ]);

            $this->id = (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Can't add post to database");
        }
    }
    public function deletePost($id)
    {
        try {
            global $pdo;
            $sql = "DELETE FROM posts WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0 ? true : false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    public static function getById($id)
    {
        try {
            global $pdo;

            $sql = "SELECT * FROM posts WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return new Posts(
                $row['profile_id'],
                $row['title'],
                $row['content'],
                $row['img_url'],
                $row['id'],
                $row['created_at'],
                $row['updated_at']
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    //edit post 

    public function editPost($id, $title, $content, $img_url)
    {
        try {
            global $pdo;
            $sql = "
                UPDATE posts
                SET title = ?,
                    content = ?,
                    img_url = ?,
                    updated_at = NOW()
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title,
                $content,
                $img_url,
                $id
            ]);
            return $stmt->rowCount() > 0 ? true : false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    public function getId()
    {
        return $this->id;
    }

    public function getProfileId()
    {
        return $this->profile_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getImgUrl()
    {
        return $this->img_url;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
}
