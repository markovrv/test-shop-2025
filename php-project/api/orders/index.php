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


require __DIR__ . '/vendor/autoload.php';

  $options = array(
    'cluster' => 'eu',
    'useTLS' => true
  );
  $pusher = new Pusher\Pusher(
    'f87abd24be3de54dc1d3',
    'dfbd37e3d24232539b19',
    '1961397',
    $options
  );


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


// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true); // Читаем данные из тела запроса

    // Получаем данные товара
    $firstName = $data['firstName'] ?? '';
    $lastName = $data['lastName'] ?? '';
    $date = $data['date'] ?? '';
    $time = $data['time'] ?? '';
    $address = $data['address'] ?? '';
    $paymentMethod = $data['paymentMethod'] ?? '';
    $goods = $data['goods'] ?? '[]';

    // Формируем SQL-запрос на вставку
    $sql = "INSERT INTO `orders` (`id`, `firstName`, `lastName`, `date`, `time`, `address`, `paymentMethod`, `goods`) VALUES (NULL, '" .
     mysqli_real_escape_string($conn, $firstName) . "', '" . 
     mysqli_real_escape_string($conn, $lastName) . "', '" . 
     mysqli_real_escape_string($conn, $date) . "', '" . 
     mysqli_real_escape_string($conn, $time) . "', '" . 
     mysqli_real_escape_string($conn, $address) . "', '" . 
     mysqli_real_escape_string($conn, $paymentMethod) . "', '" . 
     mysqli_real_escape_string($conn, $goods) . "');";


    // Выполняем запрос и проверяем результат
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'Добавлено!']);

        mail('markovrv@yandex.ru','Новый заказ', $goods);

        $data['message'] = $goods;
        $pusher->trigger('my-channel', 'my-event', $data);


    } else {
        echo json_encode(['status' => 'Ошибка добавления!']);
    }
}

// Закрываем соединение с базой данных
mysqli_close($conn);
?>