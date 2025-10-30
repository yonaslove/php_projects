<?php
session_start();

class Database {
    private $host = "localhost";
    private $db_name = "oop_login";
    private $username = "root"; // Change if needed
    private $password = "";     // Change if you have a DB password
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
        return $this->conn;
    }
}

class UserAuth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login logic
    public function login($username, $password, $remember = false) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username AND password = MD5(:password)");
        $stmt->execute(['username' => $username, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['username'] = $user['username'];

            // Remember Me
            if ($remember) {
                setcookie('username', $user['username'], time() + (86400 * 7), "/");
            }

            return true;
        }
        return false;
    }

    // Logout logic
    public function logout() {
        session_unset();
        session_destroy();
        setcookie('username', '', time() - 3600, "/");
    }

    // Check login status
    public function isLoggedIn() {
        return isset($_SESSION['username']) || isset($_COOKIE['username']);
    }

    // Get logged-in username
    public function getUser() {
        if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        } elseif (isset($_COOKIE['username'])) {
            $_SESSION['username'] = $_COOKIE['username'];
            return $_COOKIE['username'];
        }
        return null;
    }
}

// Initialize database and auth
$db = (new Database())->connect();
$auth = new UserAuth($db);

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if ($auth->login($username, $password, $remember)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "❌ Invalid username or password!";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header("Location: login.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OOP PHP Login System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: #eef2f3;
      font-family: "Poppins", sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: #fff;
      padding: 30px 40px;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.1);
      text-align: center;
      width: 340px;
    }
    input[type=text], input[type=password] {
      width: 90%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      background: #007bff;
      border: none;
      padding: 10px 20px;
      color: white;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      font-weight: bold;
    }
    button:hover {
      background: #0056b3;
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
    .logout-btn {
      background: #dc3545;
      margin-top: 15px;
    }
  </style>
</head>
<body>

<div class="container">
<?php if ($auth->isLoggedIn()): ?>
    <h2>Welcome, <?= htmlspecialchars($auth->getUser()); ?> 👋</h2>
    <form method="get">
        <button class="logout-btn" type="submit" name="logout">Logout</button>
    </form>
<?php else: ?>
    <h2>🔐 Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error; ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <label><input type="checkbox" name="remember"> Remember Me</label><br><br>
        <button type="submit" name="login">Login</button>
    </form>
<?php endif; ?>
</div>

</body>
</html>
