
<?php
error_reporting(0);

require __DIR__ . '/../vendor/autoload.php';
require   './Helper.php';

header('Content-Type:application/json');
header('Allowed-Request-Method : *');


$router = new \Bramus\Router\Router;
$connection = new App\DatabaseConnectionMYSQL;
$AppHelper = new App\Helper;
$QueryDB = $connection->connect();


$router->set404(function () {
    http_response_code(404);
    // include './404NotFound.php';
});


//? API Route Endpoints
$router->mount('/api', function () use ($router, $QueryDB, $AppHelper) {
    $router->mount('/read', function () use ($router, $QueryDB, $AppHelper) {
        //? Fetches information in the entry_logs table/view with a set limit using $limit
        $router->get('/items/entry_logs/{limit}', function ($limit) use ($QueryDB, $AppHelper) {
            //? Input
            $limit = $AppHelper->SanitizeInput($limit);
            $query = "SELECT * FROM entry_logs LIMIT $limit";
            $stmt = $QueryDB->query($query);
            if ($stmt->rowCount() !== 0) {
                $response = $AppHelper->SetResponse(200, $stmt->fetchAll());
            } else {
                $response = $AppHelper->SetResponse(404, "NO Entries");
            }

            echo json_encode($response); //? Output

        });
        //? Fetches any related information in the entries table using the $search parameter
        $router->get('/items/find_entries/{search}', function ($search) use ($QueryDB, $AppHelper) {

            $query = "SELECT * FROM entry_logs WHERE CONCAT(personnel_name,position,date_entry,time_in,time_out) LIKE ?";
            $stmt = $QueryDB->prepare($query);
            $stmt->execute(["%" . $AppHelper->SanitizeInput($search) . "%"]);

            if ($stmt->rowCount() !== 0) {
                $response = $AppHelper->SetResponse(200, $stmt->fetchAll());
            } else {
                $response = $AppHelper->SetResponse(404, "NO Entries");
            }

            echo json_encode($response); //? Output

        });
        //? Find Account/Personnel using Personnel Account Number
        $router->get('/items/find_account_no/{account_no}', function ($account_no) use ($QueryDB, $AppHelper) {

            $query = "SELECT * FROM personnel_tbl WHERE account_no = ? LIMIT 1";
            $stmt = $QueryDB->prepare($query);
            $stmt->execute([$AppHelper->SanitizeInput($account_no)]);

            if ($stmt->rowCount() !== 0) {
                $response = $AppHelper->SetResponse(200, $stmt->fetch());
            } else {
                $response = $AppHelper->SetResponse(404, "Personnel ");
            }

            echo json_encode($response); //? Output

        });
    });
    $router->mount('/create', function () use ($router, $QueryDB, $AppHelper) {
        //? Creates a new entry in the logs_tbl when a personnel times/clocks in for the day
        $router->post('/items/new_entry/time_in', function () use ($QueryDB, $AppHelper) {
            $request_body = $AppHelper->RequestBody(json_decode(file_get_contents("php://input"), true));
            $query_is_timed_in = "SELECT account_no,date_entry,time_in FROM logs_tbl WHERE account_no = ? AND date_entry = ? AND time_in IS NOT NULL LIMIT 1";
            $stmt_is_timed_in = $QueryDB->prepare($query_is_timed_in);
            $stmt_is_timed_in->execute([
                $AppHelper->SanitizeInput($request_body['account_no']),
                $AppHelper->SanitizeInput($request_body['date_entry'])
            ]);

            if ($stmt_is_timed_in->rowCount() !== 0) {
                $response = $AppHelper->SetResponse(409, "Account No. '" . $AppHelper->SanitizeInput($request_body['account_no']) . "' has already timed in.");
            } else {

                $execute_params = [
                    $AppHelper->SanitizeInput($request_body['account_no']),
                    $AppHelper->SanitizeInput($request_body['date_entry']),
                    $AppHelper->SanitizeInput($request_body['time_in']),
                    $AppHelper->SanitizeInput($request_body['time_out']),
                ];

                $insert_params = implode(",", array_keys($request_body));

                $placeholder = $AppHelper->QueryPlaceholder(array_keys($request_body), "?");

                $query = "INSERT INTO logs_tbl($insert_params) VALUES($placeholder)";
                $stmt = $QueryDB->prepare($query);
                $stmt->execute($execute_params);

                $response = $AppHelper->SetResponse(201, "Account No. '" . $AppHelper->SanitizeInput($request_body['account_no']) . "' Timed In @ " . $AppHelper->SanitizeInput($request_body['time_in']));
            }


            echo json_encode($response);
        });

        //? Creates a new entry in the personnel_tbl
        $router->post('/items/new_personnel', function () use ($QueryDB, $AppHelper) {
            $request_body = $AppHelper->RequestBody(json_decode(file_get_contents("php://input"), true));

            $query_is_existing = "SELECT personnel_name FROM personnel_tbl WHERE personnel_name = ? LIMIT 1";
            $stmt_is_existing = $QueryDB->prepare($query_is_existing);
            $stmt_is_existing->execute([
                $AppHelper->SanitizeInput($request_body['personnel_name']),
            ]);
            if ($stmt_is_existing->rowCount() !== 0) {
                $response = $AppHelper->SetResponse(409, $AppHelper->SanitizeInput($request_body['personnel_name']) . " already exists!");
            } else {
                $execute_params = [
                    $AppHelper->SanitizeInput($request_body['personnel_name']),
                    $AppHelper->SanitizeInput($request_body['department']),
                    $AppHelper->SanitizeInput($request_body['position']),
                ];

                $insert_params = implode(",", array_keys($request_body));
                $placeholder = $AppHelper->QueryPlaceholder(array_keys($request_body), "?");

                $query = "INSERT INTO personnel_tbl($insert_params) VALUES($placeholder)";
                $stmt = $QueryDB->prepare($query);
                $stmt->execute($execute_params);

                $response = $AppHelper->SetResponse(201, "Personnel '$execute_params[0]' Created");
            }

            echo json_encode($response);
        });
    });
    $router->mount('/update', function () use ($router, $QueryDB, $AppHelper) {
        //? Patches/Updates an existing entry in the personnel_tbl using the provided id/parameters
        $router->patch('/items/update_personnel/{account_no}', function ($account_no) use ($QueryDB, $AppHelper) {

            $account_no = $AppHelper->SanitizeInput($account_no);
            $request_body = $AppHelper->RequestBody(json_decode(file_get_contents("php://input"), true));

            $query = "UPDATE personnel_tbl SET personnel_name = ?, department = ?, position = ?, account_status = ? WHERE account_no = ? LIMIT 1";

            $execute_params = [
                $AppHelper->SanitizeInput($request_body['personnel_name']),
                $AppHelper->SanitizeInput($request_body['department']),
                $AppHelper->SanitizeInput($request_body['position']),
                $AppHelper->SanitizeInput($request_body['account_status']),
                $account_no
            ];

            $stmt = $QueryDB->prepare($query);
            $stmt->execute($execute_params);

            $response = $AppHelper->SetResponse(201, "Personnel '$account_no' Updated");

            echo json_encode($response);
        });
        //? Patches/Updates an existing time in entry in the logs_tb  using the provided id/parameters to time/clock out
        $router->patch('/items/time_out/{log_id}/{account_no}', function ($log_id, $account_no) use ($QueryDB, $AppHelper) {
            $log_id = $AppHelper->SanitizeInput($log_id);
            $account_no = $AppHelper->SanitizeInput($account_no);
            $request_body = $AppHelper->RequestBody(json_decode(file_get_contents("php://input"), true));

            $execute_params = [
                $AppHelper->SanitizeInput($request_body['time_out']),
                $AppHelper->SanitizeInput($request_body['status']),
                $log_id,
                $account_no
            ];

            $query = "UPDATE logs_tbl SET time_out = ?, `status` = ? WHERE log_id = ? AND account_no = ? LIMIT 1";
            $stmt = $QueryDB->prepare($query);
            $stmt->execute($execute_params);

            $response = $AppHelper->SetResponse(201, "Timed Out");

            echo json_encode($response);
        });
    });
    $router->mount('/delete', function () use ($router, $QueryDB, $AppHelper) {
        //! Deletes the personnel along with its logs/entries
        $router->delete('/items/personnel/{account_no}', function ($account_no) use ($QueryDB, $AppHelper) {
            $account_no = $AppHelper->SanitizeInput($account_no);
            $query_delete_logs = "DELETE FROM logs_tbl WHERE account_no = ? LIMIT 1";
            $stmt_delete_logs = $QueryDB->prepare($query_delete_logs);
            if ($stmt_delete_logs->execute([$account_no])) {
                $query = "DELETE FROM personnel_tbl WHERE account_no = ? LIMIT 1 ";
                $stmt = $QueryDB->prepare($query);
                $stmt->execute([$account_no]);
            }

            $response = $AppHelper->SetResponse(204, "Deleted");

            echo json_encode($response);
        });

        //! Deletes a specified entry using its primary key `log_id`
        $router->delete('/items/logs/{log_id}', function ($log_id) use ($QueryDB, $AppHelper) {
            $log_id = $AppHelper->SanitizeInput($log_id);
            $query = "DELETE FROM logs_tbl WHERE log_id = ? LIMIT 1";
            $stmt = $QueryDB->prepare($query);
            $stmt->execute([$log_id]);
            $response = $AppHelper->SetResponse(204, "Deleted");
            echo json_encode($response);
        });
    });
});

//?
$router->run();

?>