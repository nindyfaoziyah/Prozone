<?php
class Clan {
    private $conn;
    private $table_name = "clans";

    public $id;
    public $nama_clan;
    public $slug;
    public $deskripsi;
    public $avatar;
    public $leader_id;
    public $total_members;
    public $total_xp;
    public $is_public;
    public $max_members;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET nama_clan=:nama_clan, slug=:slug, deskripsi=:deskripsi,
                      avatar=:avatar, leader_id=:leader_id, is_public=:is_public,
                      max_members=:max_members";

        $stmt = $this->conn->prepare($query);

        $this->nama_clan = strip_tags($this->nama_clan);
        $this->slug = $this->generateSlug($this->nama_clan);
        $this->deskripsi = strip_tags($this->deskripsi);
        $this->avatar = !empty($this->avatar) ? strip_tags($this->avatar) : null;
        $this->leader_id = strip_tags($this->leader_id);
        $this->is_public = isset($this->is_public) ? 1 : 0;
        $this->max_members = strip_tags($this->max_members ?? 50);

        $stmt->bindParam(':nama_clan', $this->nama_clan);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':avatar', $this->avatar);
        $stmt->bindParam(':leader_id', $this->leader_id);
        $stmt->bindParam(':is_public', $this->is_public);
        $stmt->bindParam(':max_members', $this->max_members);

        if ($stmt->execute()) {
            $clan_id = $this->conn->lastInsertId();
            // Add leader as member
            $this->addMember($clan_id, $this->leader_id, 'leader');
            
            // Check achievement for creating clan
            require_once 'Achievement.php';
            $achievement = new Achievement($this->conn);
            $achievement->kode_achievement = 'clan_leader';
            $achievement->checkAndAward($this->leader_id, 'clan_leader');
            
            return $clan_id;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT c.*, u.nama_lengkap as leader_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.leader_id = u.id
                  WHERE c.is_public = 1
                  ORDER BY c.total_xp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Admin: Read ALL clans (public + private) with member counts
     */
    public function readAllAdmin() {
        $query = "SELECT c.*, u.nama_lengkap as leader_name,
                         (SELECT COUNT(*) FROM clan_members WHERE clan_id = c.id) as member_count
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.leader_id = u.id
                  ORDER BY c.total_xp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Admin: Search clans by name
     */
    public function search($keyword) {
        $query = "SELECT c.*, u.nama_lengkap as leader_name,
                         (SELECT COUNT(*) FROM clan_members WHERE clan_id = c.id) as member_count
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.leader_id = u.id
                  WHERE c.nama_clan LIKE :keyword
                     OR c.deskripsi LIKE :keyword
                     OR u.nama_lengkap LIKE :keyword
                  ORDER BY c.total_xp DESC";

        $stmt = $this->conn->prepare($query);
        $keyword = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Admin: Update clan info
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nama_clan=:nama_clan, slug=:slug, deskripsi=:deskripsi,
                      is_public=:is_public, max_members=:max_members
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nama_clan = strip_tags($this->nama_clan);
        $this->slug = $this->generateSlug($this->nama_clan);
        $this->deskripsi = strip_tags($this->deskripsi);
        $this->is_public = isset($this->is_public) ? 1 : 0;
        $this->max_members = strip_tags($this->max_members ?? 50);
        $this->id = strip_tags($this->id);

        $stmt->bindParam(':nama_clan', $this->nama_clan);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':is_public', $this->is_public);
        $stmt->bindParam(':max_members', $this->max_members);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Admin: Delete clan and all members
     */
    public function delete() {
        // First delete all members
        $query_members = "DELETE FROM clan_members WHERE clan_id = :id";
        $stmt_members = $this->conn->prepare($query_members);
        $stmt_members->bindParam(':id', $this->id);
        $stmt_members->execute();

        // Then delete the clan
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT c.*, u.nama_lengkap as leader_name
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.leader_id = u.id
                  WHERE c.id = :id OR c.slug = :slug";

        $stmt = $this->conn->prepare($query);
        $id = is_numeric($this->id) ? $this->id : 0;
        $slug = $this->slug ?? '';
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function addMember($clan_id, $user_id, $role = 'member') {
        $query = "INSERT INTO clan_members (clan_id, user_id, role)
                  VALUES (:clan_id, :user_id, :role)
                  ON DUPLICATE KEY UPDATE role=:role";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clan_id', $clan_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            // Update total members
            $this->updateMemberCount($clan_id);
            return true;
        }
        return false;
    }

    public function removeMember($clan_id, $user_id) {
        $query = "DELETE FROM clan_members WHERE clan_id = :clan_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clan_id', $clan_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $this->updateMemberCount($clan_id);
            return true;
        }
        return false;
    }

    public function getMembers($clan_id) {
        $query = "SELECT cm.*, u.username, u.nama_lengkap, u.avatar, u.total_xp, 
                         u.is_online, u.last_seen
                  FROM clan_members cm
                  JOIN users u ON cm.user_id = u.id
                  WHERE cm.clan_id = :clan_id
                  ORDER BY u.is_online DESC, cm.role DESC, cm.xp_contribution DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clan_id', $clan_id);
        $stmt->execute();

        return $stmt;
    }

    public function isMember($clan_id, $user_id) {
        $query = "SELECT * FROM clan_members WHERE clan_id = :clan_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clan_id', $clan_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function updateMemberCount($clan_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET total_members = (SELECT COUNT(*) FROM clan_members WHERE clan_id = :clan_id)
                  WHERE id = :clan_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clan_id', $clan_id);
        $stmt->execute();
    }

    private function generateSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}
?>

