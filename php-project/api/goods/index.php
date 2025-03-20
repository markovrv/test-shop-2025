<?php
// Заголовки CORS для разрешения запросов из других источников
header("Access-Control-Allow-Origin: *"); // Разрешаем доступ с любого источника
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE"); // Разрешаем указанные методы
header("Access-Control-Allow-Headers: Content-Type"); // Разрешаем указанные заголовки

// Если запрос является предзапросом (OPTIONS), завершаем выполнение скрипта
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

header("Content-Type: application/json"); // Устанавливаем заголовок типа контента как JSON

// Подключение к MySQL
$servername = "localhost"; // Локальный сервер
$username = "shop_vue"; // Имя пользователя БД
$password = "shop_vue"; // Пароль БД
$dbname = "shop_vue"; // Название базы данных

// Создание соединения с базой данных
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Проверяем соединение
if (!$conn) {
    die(json_encode(['status' => 'Ошибка соединения: ' . mysqli_connect_error()]));
}

// Обработка GET-запросов
if ($_SERVER['REQUEST_METHOD'] == "GET") {

    $sql = "SELECT * FROM goods";

    $result = mysqli_query($conn, $sql); // Выполняем запрос
    $arr = []; // Массив для хранения результатов

    // Проверяем наличие результатов и формируем массив
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arr[] = [
                "id" => $row["id"],
                "title" => $row["title"],
                "description" => $row["description"],
                "price" => $row["price"],
                "image" => $row["image"],
            ];
        }
    }

    echo json_encode($arr); // Выводим результаты в формате JSON
}

// Обработка DELETE-запросов
if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    $id = $_GET['id'] ?? null; // Получаем ID из параметров запроса

    if ($id > 0) {
        $sql = "DELETE FROM goods WHERE id=" . intval($id); // Формируем SQL-запрос на удаление
        $result = mysqli_query($conn, $sql); // Выполняем запрос

        // Проверяем результат выполнения запроса
        if ($result) {
            echo json_encode(['status' => 'Удалено успешно!']);
        } else {
            echo json_encode(['status' => 'Ошибка удаления!']);
        }
    } else {
        echo json_encode(['status' => 'Ошибка ID!']);
    }
}

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true); // Читаем данные из тела запроса

    // Получаем данные товара
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $image = $data['image'] ?? 0;

    // Формируем SQL-запрос на вставку
    $sql = "INSERT INTO goods (title, description, price, image) VALUES ('" . mysqli_real_escape_string($conn, $title) . "', '" . mysqli_real_escape_string($conn, $description) . "', " . floatval($price) . ", '" . mysqli_real_escape_string($conn, $image) . "')";

    // Выполняем запрос и проверяем результат
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'Добавлено!']);
    } else {
        echo json_encode(['status' => 'Ошибка добавления!']);
    }
}

// Закрываем соединение с базой данных
mysqli_close($conn);
?>