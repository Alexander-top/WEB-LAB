<?php
// config.php - Настройки подключения к БД

define('DB_HOST', 'localhost');
define('DB_NAME', 'u82308');
define('DB_USER', 'u82308');
define('DB_PASS', '8913647');
define('DB_CHARSET', 'utf8mb4');

// Функция для получения соединения с БД
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

// Генерация логина (10 заглавных букв)
function generateLogin() {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $login = '';
    for ($i = 0; $i < 10; $i++) {
        $login .= $letters[random_int(0, 25)];
    }
    return $login;
}

// Генерация пароля (10 символов)
function generatePassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, 10);
}

// Создание нового пользователя
function createUser($pdo) {
    $login = generateLogin();
    $plainPassword = generatePassword();
    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
    $stmt->execute([$login, $passwordHash]);
    
    return [
        'user_id' => $pdo->lastInsertId(),
        'login' => $login,
        'password' => $plainPassword
    ];
}

// Авторизация обычного пользователя
function authenticateUser($pdo, $login, $password) {
    $stmt = $pdo->prepare("SELECT id, login, password_hash FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_authenticated'] = true;
        return true;
    }
    return false;
}

// Проверка авторизации пользователя
function isAuthenticated() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
}

// Выход
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
}

// Получение заявки пользователя
function getUserApplication($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Получение языков заявки
function getApplicationLanguages($pdo, $application_id) {
    $stmt = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
    $stmt->execute([$application_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Сохранение заявки
function saveApplication($pdo, $data, $user_id, $application_id = null) {
    $pdo->beginTransaction();
    
    try {
        if ($application_id) {
            $sql = "UPDATE applications SET 
                    full_name = ?, phone = ?, email = ?, birth_date = ?, 
                    gender = ?, bio = ?, contract_accepted = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['full_name'], $data['phone'], $data['email'], $data['birth_date'],
                $data['gender'], $data['bio'], $data['contract_accepted'], $application_id
            ]);
            
            $stmt = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt->execute([$application_id]);
            $appId = $application_id;
        } else {
            $sql = "INSERT INTO applications 
                    (user_id, full_name, phone, email, birth_date, gender, bio, contract_accepted) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id, $data['full_name'], $data['phone'], $data['email'],
                $data['birth_date'], $data['gender'], $data['bio'], $data['contract_accepted']
            ]);
            $appId = $pdo->lastInsertId();
        }
        
        if (!empty($data['languages'])) {
            $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($data['languages'] as $lang_id) {
                $stmt->execute([$appId, $lang_id]);
            }
        }
        
        $pdo->commit();
        return $appId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Все языки
function getAllLanguages() {
    return [
        1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript',
        5 => 'PHP', 6 => 'Python', 7 => 'Java', 8 => 'Haskell',
        9 => 'Clojure', 10 => 'Prolog', 11 => 'Scala', 12 => 'Go'
    ];
}
?>