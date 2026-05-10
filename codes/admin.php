<?php
/**
 * Задача 6. Реализовать вход администратора с использованием
 * HTTP-авторизации для просмотра и удаления результатов.
 **/

require_once 'config.php';

// HTTP Basic Authentication
$valid_login = 'admin';
$valid_password = '123';

if (empty($_SERVER['PHP_AUTH_USER']) || 
    empty($_SERVER['PHP_AUTH_PW']) || 
    $_SERVER['PHP_AUTH_USER'] !== $valid_login || 
    $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    print('<h1>401 Требуется авторизация</h1>');
    print('<p>Для доступа к панели администратора введите логин и пароль.</p>');
    print('<p>Логин: admin<br>Пароль: 123</p>');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #667eea;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #667eea;
            padding: 20px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 24px; }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
        }
        .logout-btn:hover { background: #c82333; }
        .section {
            padding: 30px;
            border-bottom: 1px solid #eee;
        }
        .section h2 { margin-bottom: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th { background: #f8f9fa; }
        table tr:hover { background: #f5f5f5; }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-delete:hover { background: #c82333; }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px 25px;
            min-width: 100px;
            text-align: center;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .success {
            background: #f8f9fa;
            color: #f8f9fa;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        .bio-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Панель администратора</h1>
        <div>
            <span>Вы вошли как: <strong>admin</strong></span>
            <a href="?logout=1" class="logout-btn">Выйти</a>
        </div>
    </div>

    <?php
    // Обработка выхода
    if (isset($_GET['logout'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        print('<h1>401 Требуется авторизация</h1>');
        exit();
    }

    $pdo = getDBConnection();
    $message = '';
    $error = '';

    // Обработка удаления
    if (isset($_GET['delete_id'])) {
        $id = (int)$_GET['delete_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$id]);
            $message = " ";
        } catch (Exception $e) {
            $error = "Ошибка при удалении";
        }
    }

    // Получаем все заявки
    $stmt = $pdo->query("
        SELECT a.*, u.login as user_login 
        FROM applications a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.id DESC
    ");
    $applications = $stmt->fetchAll();

    // Статистика по языкам
    $stmt = $pdo->query("
        SELECT pl.name, COUNT(al.application_id) as cnt
        FROM programming_languages pl
        LEFT JOIN application_languages al ON pl.id = al.language_id
        GROUP BY pl.id, pl.name
        ORDER BY cnt DESC
    ");
    $stats = $stmt->fetchAll();
    ?>

    <div class="section">
        <?php if ($message): ?>
            <div class="success"> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h2>Все заявки пользователей</h2>
        <?php if (empty($applications)): ?>
            <p>Нет заявок.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Дата рождения</th>
                        <th>Пол</th>
                        <th>Языки</th>
                        <th>Биография</th>
                        <th>Контракт</th>
                        <th>Дата</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): 
                        $stmt = $pdo->prepare("
                            SELECT pl.name FROM application_languages al
                            JOIN programming_languages pl ON al.language_id = pl.id
                            WHERE al.application_id = ?
                        ");
                        $stmt->execute([$app['id']]);
                        $langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        $languages_str = implode(', ', $langs);
                        
                        // Обрезаем биографию без mb_substr
                        $bio_preview = strlen($app['bio']) > 50 ? substr($app['bio'], 0, 50) . '...' : $app['bio'];
                    ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><?php echo htmlspecialchars($app['user_login'] ?? 'Нет'); ?></td>
                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['phone']); ?></td>
                            <td><?php echo htmlspecialchars($app['email']); ?></td>
                            <td><?php echo $app['birth_date']; ?></td>
                            <td><?php echo $app['gender'] === 'male' ? 'Мужской' : 'Женский'; ?></td>
                            <td><?php echo htmlspecialchars($languages_str); ?></td>
                            <td class="bio-preview" title="<?php echo htmlspecialchars($app['bio']); ?>">
                                <?php echo htmlspecialchars($bio_preview); ?>
                            </td>
                            <td><?php echo $app['contract_accepted'] ? 'Да' : 'Нет'; ?></td>
                            <td><?php echo $app['created_at']; ?></td>
                            <td>
                                <a href="?delete_id=<?php echo $app['id']; ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Удалить заявку #<?php echo $app['id']; ?>?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Статистика -->
    <div class="section">
        <h2>Статистика по языкам программирования</h2>
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stat['cnt']; ?></div>
                    <div><?php echo htmlspecialchars($stat['name']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top: 20px; color: #666;">Всего заявок: <?php echo count($applications); ?></p>
    </div>
</div>
</body>
</html>