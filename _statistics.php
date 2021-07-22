<?php

$databaseHost = '';
$databaseUserRead = '';
$databasePasswordRead = '';
$databaseName = '';

function executeGetQuery($query) {
    global $databaseHost, $databaseUserRead, $databasePasswordRead, $databaseName;
    $conn = mysqli_connect($databaseHost, $databaseUserRead, $databasePasswordRead, $databaseName);
    
    mysqli_set_charset($conn, 'utf8');

    $result = mysqli_query($conn, $query);
    // die(print_r($query));
    $result = mysqli_fetch_assoc($result);

    mysqli_close($conn);

    return $result;
}


function executeGetMultipleRowsQuery($query) {
    global $databaseHost, $databaseUserRead, $databasePasswordRead, $databaseName;
    $conn =  mysqli_connect($databaseHost, $databaseUserRead, $databasePasswordRead, $databaseName);
    mysqli_set_charset($conn, 'utf8');

    $result = mysqli_query($conn, $query);

    mysqli_close($conn);

    return $result;
}

function getGeneralStats()
{
    $query = <<<SQL
        SELECT COUNT(1) as count
        FROM user
        WHERE deleted = 0
            AND guild_index <> 1;
SQL;


    $users = executeGetQuery($query);

    $query = <<<SQL
        SELECT COUNT(1) as count
        FROM account;
SQL;
    $accounts = executeGetQuery($query);

//     $query = <<<SQL
//         SELECT clanes_creados
//         FROM stats;
// SQL;
//     $clanes = executeGetQuery($query);

    return array(
        'accounts' => $accounts['count'],
        'users' => $users['count'],
        // 'clanes' => $clanes['clanes_creados']
    );
}

function getUsuariosPorClase()
{
    $query = <<<SQL
        SELECT class_id, COUNT(id) as count
        FROM user
        WHERE class_id <> 11
            AND level > 25
            AND deleted = false
            AND guild_index <> 1
        GROUP BY class_id
        ORDER BY class_id;
SQL;

    $usuariosPorClase = executeGetMultipleRowsQuery($query);

    $result = array();

    foreach ($usuariosPorClase as $entry) {
        $result[] = array(
            'name' => getClase($entry['class_id']),
            'y' => intval($entry['count'])
        );
    }

    return $result;
}

function getClasesPorRaza()
{
    $query = <<<SQL
        SELECT race_id, class_id, COUNT(id) as count
        FROM user
        WHERE class_id <> 11
            AND level > 25
            AND deleted = false
            AND guild_index <> 1
        GROUP BY race_id, class_id
        ORDER BY race_id, class_id;
SQL;

    $clasesPorRaza = executeGetMultipleRowsQuery($query);

    $result = array();

    for ($i = 1; $i < 7 ; $i++) {
        $arrayClases = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $result[$i] = array(
            'name' => getRaza($i),
            'data' => $arrayClases
        );
    }

    foreach ($clasesPorRaza as $entry) {
        $result[$entry['race_id']]['data'][$entry['class_id'] - 1] = intval($entry['count']);
    }

    $result = array_values($result);

    return $result;
}

function getUsuariosPorLevel()
{
    $query = <<<SQL
        SELECT level, COUNT(id) as count
        FROM user
        WHERE level > 13
            AND deleted = false
            AND guild_index <> 1
        GROUP BY level
        ORDER BY level ASC;
SQL;

    $usuariosPorLevel = executeGetMultipleRowsQuery($query);

    $result = array();

    for ($i = 14; $i <= 54 ; $i++) {
        $result[] = 0;
    }

    foreach ($usuariosPorLevel as $entry) {
        $result[$entry['level'] - 14] = intval($entry['count']);
    }

    return $result;
}

function getKillsPorClase()
{
    $query = <<<SQL
        SELECT class_id, AVG(ciudadanos_matados + criminales_matados) as promedio_matados
        FROM user
        WHERE level >= 25
            AND deleted = FALSE
            AND guild_index <> 1
        GROUP BY class_id
        HAVING promedio_matados >= 1
        ORDER BY AVG(ciudadanos_matados + criminales_matados) DESC;
SQL;

    $killsPorClase = executeGetMultipleRowsQuery($query);

    $result = array();

    foreach ($killsPorClase as $entry) {
        $result[] = array(
            'name' => getClase($entry['class_id']),
            'y' => intval($entry['promedio_matados'])
        );
    }

    return $result;
}

function getUsuariosOnlinePorHora()
{
    $query = <<<SQL
        SELECT HOUR(date) as hora, AVG(number) as users
        FROM users_online
        GROUP BY HOUR(date);
SQL;

    $usuariosPorHora = executeGetMultipleRowsQuery($query);

    $result = array();

    for ($i = 0; $i <= 23; $i++) {
        $result[] = 0;
    }

    foreach ($usuariosPorHora as $entry) {
        $result[$entry['hora']] = floatval($entry['users']);
    }

    return $result;
}