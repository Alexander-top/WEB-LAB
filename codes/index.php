<?php
// index.php - Главная страница с формой

require_once 'config.php';

// Массивы для полей формы
$fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract'];
$multiple_fields = ['languages'];

// Обработка выхода
if (isset($_GET['logout'])) {
    logout();
    header('Location: index.php');
    exit();
}

// GET запрос - показ формы
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    $errors = [];
    $error_messages = [];
    $values = [];
    
    // Сообщения из Cookies
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';
    }
    if (!empty($_COOKIE['credentials'])) {
        $messages[] = urldecode($_COOKIE['credentials']);
        setcookie('credentials', '', 100000);
    }
    if (!empty($_COOKIE['auth_error'])) {
        $messages[] = $_COOKIE['auth_error'];
        setcookie('auth_error', '', 100000);
    }
    if (!empty($_COOKIE['save_error'])) {
        $messages[] = 'Ошибка при сохранении данных.';
        setcookie('save_error', '', 100000);
    }
    
    // Ошибки полей
    foreach ($fields as $field) {
        if (!empty($_COOKIE[$field . '_error'])) {
            $errors[$field] = true;
            $error_messages[$field] = $_COOKIE[$field . '_message'] ?? '';
            setcookie($field . '_error', '', 100000);
            setcookie($field . '_message', '', 100000);
        }
    }
    
    // Значения полей
    foreach ($fields as $field) {
        if (in_array($field, $multiple_fields)) {
            $values[$field] = isset($_COOKIE[$field . '_value']) ? explode(',', $_COOKIE[$field . '_value']) : [];
        } else {
            $values[$field] = $_COOKIE[$field . '_value'] ?? '';
        }
    }
    
    // Если пользователь авторизован - загружаем его данные
    if (isAuthenticated()) {
        try {
            $pdo = getDBConnection();
            $userData = getUserApplication($pdo);
            if ($userData) {
                $values['fio'] = $userData['full_name'];
                $values['phone'] = $userData['phone'];
                $values['email'] = $userData['email'];
                $values['birthdate'] = $userData['birth_date'];
                $values['gender'] = $userData['gender'];
                $values['bio'] = $userData['bio'];
                $values['contract'] = $userData['contract_accepted'] ? 'on' : '';
                $values['languages'] = getApplicationLanguages($pdo, $userData['id']);
                $messages[] = "Вы авторизованы как: " . $_SESSION['user_login'] . ". Редактируйте данные.";
            } else {
                $messages[] = "Вы авторизованы как: " . $_SESSION['user_login'] . ". Заполните форму.";
            }
        } catch (Exception $e) {
            $messages[] = 'Ошибка загрузки данных.';
        }
    }
    
    include('form.php');
}

// POST запрос - обработка формы
else {
    $login_action = isset($_POST['login_action']);
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Обработка входа
    if ($login_action && !empty($login) && !empty($password)) {
        try {
            $pdo = getDBConnection();
            
            // Проверка обычного пользователя
            if (authenticateUser($pdo, $login, $password)) {
                header('Location: index.php');
                exit();
            }
            
            setcookie('auth_error', 'Неверный логин или пароль', time() + 86400);
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            setcookie('auth_error', 'Ошибка при авторизации', time() + 86400);
            header('Location: index.php');
            exit();
        }
    }
    
    // Получение данных формы
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $language_ids = $_POST['languages'] ?? [];
    $bio = trim($_POST['bio'] ?? '');
    $contract = isset($_POST['contract']) ? 1 : 0;
    
    // Сохраняем в Cookies
    setcookie('fio_value', $fio, time() + 365*86400);
    setcookie('phone_value', $phone, time() + 365*86400);
    setcookie('email_value', $email, time() + 365*86400);
    setcookie('birthdate_value', $birthdate, time() + 365*86400);
    setcookie('gender_value', $gender, time() + 365*86400);
    setcookie('languages_value', implode(',', $language_ids), time() + 365*86400);
    setcookie('bio_value', $bio, time() + 365*86400);
    setcookie('contract_value', $contract ? 'on' : '', time() + 365*86400);
    
    // Очищаем старые ошибки
    foreach (['fio', 'phone', 'email', 'birthdate', 'gender', 'bio', 'contract', 'languages'] as $f) {
        setcookie($f . '_error', '', 100000);
        setcookie($f . '_message', '', 100000);
    }
    
    $errors = false;
    
    // Валидация ФИО
    if (empty($fio)) {
        setcookie('fio_error', '1', time() + 86400);
        setcookie('fio_message', 'ФИО обязательно', time() + 86400);
        $errors = true;
    } elseif (strlen($fio) > 150) {
        setcookie('fio_error', '1', time() + 86400);
        setcookie('fio_message', 'ФИО не более 150 символов', time() + 86400);
        $errors = true;
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]+$/u', $fio)) {
        setcookie('fio_error', '1', time() + 86400);
        setcookie('fio_message', 'Только буквы, пробелы и дефисы', time() + 86400);
        $errors = true;
    }
    
    // Валидация телефона
    if (empty($phone)) {
        setcookie('phone_error', '1', time() + 86400);
        setcookie('phone_message', 'Телефон обязателен', time() + 86400);
        $errors = true;
    } else {
        $phone_clean = preg_replace('/[^0-9+]/', '', $phone);
        if (!preg_match('/^\+7[0-9]{10}$/', $phone_clean) && !preg_match('/^8[0-9]{10}$/', $phone_clean)) {
            setcookie('phone_error', '1', time() + 86400);
            setcookie('phone_message', 'Формат: +7XXXXXXXXXX или 8XXXXXXXXXX', time() + 86400);
            $errors = true;
        }
    }
    
    // Валидация email
    if (empty($email)) {
        setcookie('email_error', '1', time() + 86400);
        setcookie('email_message', 'Email обязателен', time() + 86400);
        $errors = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 86400);
        setcookie('email_message', 'Некорректный email', time() + 86400);
        $errors = true;
    }
    
    // Валидация даты рождения
    if (empty($birthdate)) {
        setcookie('birthdate_error', '1', time() + 86400);
        setcookie('birthdate_message', 'Дата рождения обязательна', time() + 86400);
        $errors = true;
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
        $today = new DateTime();
        if (!$date || $date > $today) {
            setcookie('birthdate_error', '1', time() + 86400);
            setcookie('birthdate_message', 'Некорректная дата', time() + 86400);
            $errors = true;
        }
    }
    
    // Валидация пола
    if (empty($gender)) {
        setcookie('gender_error', '1', time() + 86400);
        setcookie('gender_message', 'Выберите пол', time() + 86400);
        $errors = true;
    } elseif (!in_array($gender, ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 86400);
        setcookie('gender_message', 'Некорректный пол', time() + 86400);
        $errors = true;
    }
    
    // Валидация языков
    if (empty($language_ids)) {
        setcookie('languages_error', '1', time() + 86400);
        setcookie('languages_message', 'Выберите язык', time() + 86400);
        $errors = true;
    }
    
    // Валидация биографии
    if (empty($bio)) {
        setcookie('bio_error', '1', time() + 86400);
        setcookie('bio_message', 'Биография обязательна', time() + 86400);
        $errors = true;
    } elseif (strlen($bio) > 1000) {
        setcookie('bio_error', '1', time() + 86400);
        setcookie('bio_message', 'Не более 1000 символов', time() + 86400);
        $errors = true;
    }
    
    // Валидация чекбокса
    if (!$contract) {
        setcookie('contract_error', '1', time() + 86400);
        setcookie('contract_message', 'Примите условия', time() + 86400);
        $errors = true;
    }
    
    if ($errors) {
        header('Location: index.php');
        exit();
    }
    
    // Сохранение в БД
    try {
        $pdo = getDBConnection();
        
        if (isAuthenticated()) {
            $user_id = $_SESSION['user_id'];
            $existing = getUserApplication($pdo);
            $app_id = $existing ? $existing['id'] : null;
        } else {
            $userCreds = createUser($pdo);
            $user_id = $userCreds['user_id'];
            $plain_password = $userCreds['password'];
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_login'] = $userCreds['login'];
            $_SESSION['user_authenticated'] = true;
        }
        
        $saveData = [
            'full_name' => $fio,
            'phone' => preg_replace('/[^0-9+]/', '', $phone),
            'email' => $email,
            'birth_date' => $birthdate,
            'gender' => $gender,
            'bio' => $bio,
            'contract_accepted' => $contract,
            'languages' => $language_ids
        ];
        
        saveApplication($pdo, $saveData, $user_id, $app_id ?? null);
        
        if (isset($plain_password)) {
            $msg = "ВАШИ ДАННЫЕ ДЛЯ ВХОДА:\nЛогин: " . $_SESSION['user_login'] . "\nПароль: " . $plain_password . "\n\nСохраните их!";
            setcookie('credentials', urlencode($msg), time() + 60);
        }
        
        setcookie('save', '1');
        
    } catch (Exception $e) {
        setcookie('save_error', '1', time() + 86400);
    }
    
    header('Location: index.php');
    exit();
}
?>