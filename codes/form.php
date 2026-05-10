<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма заявки</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #667eea;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: #667eea;
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .auth-section {
            padding: 20px 40px 0 40px;
            border-bottom: 1px solid #eee;
        }
        
        .auth-card {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 10px;
        }
        
        .auth-card h4 {
            margin: 0 0 15px 0;
        }
        
        .auth-login-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .auth-login-form input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 16px;
            min-width: 200px;
        }
        
        .auth-login-form button {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .auth-login-form button:hover {
            background: #5a67d8;
        }
        
        .auth-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #e8f5e9;
            padding: 15px 20px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            cursor: pointer;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        form {
            padding: 40px;
        }
        
        .messages {
            margin-bottom: 30px;
            padding: 0 40px;
        }
        
        .message {
            padding: 15px 20px;
            margin-bottom: 10px;
            white-space: pre-line;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .form-row {
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 15px;
        }
        
        .form-row label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #333;
            padding-top: 12px;
        }
        
        .form-row label.required::after {
            content: " *";
            color: #dc3545;
        }
        
        .form-row .field {
            flex: 1;
            min-width: 300px;
        }
        
        .form-row .field.error input,
        .form-row .field.error select,
        .form-row .field.error textarea {
            border-color: #dc3545;
            background-color: #fff8f8;
        }
        
        .error-text {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            font-size: 16px;
            font-family: inherit;
            background: #fafafa;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .radio-group {
            display: flex;
            gap: 30px;
            padding: 8px 0;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        select[multiple] {
            height: 200px;
        }
        
        select[multiple] option:checked {
            background: #667eea;
            color: white;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }
        
        .hint {
            font-size: 13px;
            color: #888;
            margin-top: 6px;
        }
        
        .form-actions {
            margin-top: 35px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 30px;
        }
        
        .save-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 16px 50px;
            font-size: 18px;
            cursor: pointer;
        }
        
        .save-btn:hover {
            background: #218838;
        }
        
        .char-counter {
            font-size: 12px;
            color: #888;
            text-align: right;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            form, .messages {
                padding: 20px;
            }
            .auth-section {
                padding: 15px 20px 0 20px;
            }
            .form-row {
                flex-direction: column;
            }
            .form-row label {
                flex: none;
            }
            .form-row .field {
                min-width: 100%;
            }
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            .auth-login-form {
                flex-direction: column;
            }
            .auth-login-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Форма заявки</h1>
            <p>Заполните все обязательные поля</p>
        </div>
        
        <div class="auth-section">
            <?php if (isAuthenticated()): ?>
                <div class="auth-status">
                    <div>
                        Вы авторизованы как: <strong><?php echo htmlspecialchars($_SESSION['user_login'] ?? ''); ?></strong>
                    </div>
                    <form action="index.php" method="GET" style="margin: 0;">
                        <button type="submit" name="logout" value="1" class="logout-btn">Выйти</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="auth-card">
                    <h4>Вход для редактирования</h4>
                    <form action="index.php" method="POST" id="loginForm">
                        <div class="auth-login-form">
                            <input type="text" name="login" id="login" placeholder="Логин" autocomplete="off">
                            <input type="password" name="password" id="password" placeholder="Пароль" autocomplete="off">
                            <button type="submit" name="login_action" value="1">Войти</button>
                        </div>
                        <div class="hint">Введите логин и пароль, полученные при отправке формы</div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($messages)): ?>
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message 
                        <?php echo strpos($msg, 'Спасибо') !== false ? 'success' : ''; ?>
                        <?php echo strpos($msg, 'Вход') !== false ? 'info' : ''; ?>
                    ">
                        <?php echo nl2br(htmlspecialchars($msg)); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php" method="POST">
            <div class="form-row">
                <label for="fio" class="required">ФИО</label>
                <div class="field <?php echo !empty($errors['fio']) ? 'error' : ''; ?>">
                    <input type="text" id="fio" name="fio" value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>" maxlength="150">
                    <div class="hint">Только буквы, пробелы и дефисы. Не более 150 символов</div>
                    <?php if (!empty($error_messages['fio'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['fio']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="phone" class="required">Телефон</label>
                <div class="field <?php echo !empty($errors['phone']) ? 'error' : ''; ?>">
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>" placeholder="+7 (999) 123-45-67">
                    <div class="hint">Формат: +7XXXXXXXXXX или 8XXXXXXXXXX</div>
                    <?php if (!empty($error_messages['phone'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="email" class="required">Email</label>
                <div class="field <?php echo !empty($errors['email']) ? 'error' : ''; ?>">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>" placeholder="example@domain.com">
                    <?php if (!empty($error_messages['email'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="birthdate" class="required">Дата рождения</label>
                <div class="field <?php echo !empty($errors['birthdate']) ? 'error' : ''; ?>">
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>">
                    <?php if (!empty($error_messages['birthdate'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['birthdate']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label class="required">Пол</label>
                <div class="field <?php echo !empty($errors['gender']) ? 'error' : ''; ?>">
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="male" name="gender" value="male" <?php echo (($values['gender'] ?? '') == 'male') ? 'checked' : ''; ?>>
                            <label for="male">Мужской</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="female" name="gender" value="female" <?php echo (($values['gender'] ?? '') == 'female') ? 'checked' : ''; ?>>
                            <label for="female">Женский</label>
                        </div>
                    </div>
                    <?php if (!empty($error_messages['gender'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['gender']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="languages" class="required">Любимые ЯП</label>
                <div class="field <?php echo !empty($errors['languages']) ? 'error' : ''; ?>">
                    <select name="languages[]" id="languages" multiple>
                        <?php
                        $all_langs = [1=>'Pascal',2=>'C',3=>'C++',4=>'JavaScript',5=>'PHP',6=>'Python',7=>'Java',8=>'Haskell',9=>'Clojure',10=>'Prolog',11=>'Scala',12=>'Go'];
                        $selected_langs = $values['languages'] ?? [];
                        foreach ($all_langs as $id => $name) {
                            $selected = in_array($id, $selected_langs) ? 'selected' : '';
                            echo "<option value=\"$id\" $selected>$name</option>";
                        }
                        ?>
                    </select>
                    <div class="hint">Выберите один или несколько (Ctrl+Click)</div>
                    <?php if (!empty($error_messages['languages'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['languages']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label for="bio" class="required">Биография</label>
                <div class="field <?php echo !empty($errors['bio']) ? 'error' : ''; ?>">
                    <textarea id="bio" name="bio" maxlength="1000"><?php echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
                    <div class="char-counter"><?php echo strlen($values['bio'] ?? ''); ?>/1000</div>
                    <div class="hint">Не более 1000 символов</div>
                    <?php if (!empty($error_messages['bio'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['bio']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <label></label>
                <div class="field <?php echo !empty($errors['contract']) ? 'error' : ''; ?>">
                    <div class="checkbox-option">
                        <input type="checkbox" id="contract" name="contract" value="on" <?php echo !empty($values['contract']) ? 'checked' : ''; ?>>
                        <label for="contract">Я ознакомлен(а) с контрактом *</label>
                    </div>
                    <?php if (!empty($error_messages['contract'])): ?>
                        <div class="error-text"><?php echo htmlspecialchars($error_messages['contract']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="save-btn">Сохранить</button>
                <div class="hint" style="margin-top: 15px;">
                    <?php if (isAuthenticated()): ?>
                        Вы редактируете свои данные.
                    <?php else: ?>
                        При первой отправке вы получите логин и пароль.
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bio = document.getElementById('bio');
            const counter = document.querySelector('.char-counter');
            if (bio && counter) {
                bio.addEventListener('input', function() {
                    counter.textContent = bio.value.length + '/1000';
                });
            }
        });
    </script>
</body>
</html>