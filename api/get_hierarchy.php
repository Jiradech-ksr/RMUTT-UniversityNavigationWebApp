<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'upload_helper.php';
include 'db_connect.php';

function makeFullUrl($dbPath)
{
    if (empty($dbPath)) return null;
    if (strpos($dbPath, 'http') === 0) return $dbPath;

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $baseDir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
    return $protocol . "://" . $host . $baseDir . "/" . ltrim($dbPath, '/');
}

$result = [];

// 1. Fetch all Faculties
$sql_faculties = "SELECT * FROM faculties ORDER BY name ASC";
$res_faculties = $conn->query($sql_faculties);
if (!$res_faculties) {
    die(json_encode(["error" => "Faculty Query Failed: " . $conn->error]));
}

while ($faculty = $res_faculties->fetch_assoc()) {
    $facultyNode = [
        "id"       => $faculty['id'],
        "title_en" => $faculty['name'],
        "title_th" => $faculty['name'],
        "type"     => "faculty",
        "children" => []
    ];

    // 2. Fetch Departments under this Faculty
    $fid = (int)$faculty['id'];
    $res_depts = $conn->query("SELECT * FROM departments WHERE faculty_id = $fid ORDER BY name ASC");

    if ($res_depts) {
        while ($dept = $res_depts->fetch_assoc()) {
            $deptNode = [
                "id"       => $dept['id'],
                "title_en" => $dept['name'],
                "title_th" => $dept['name'],
                "type"     => "department",
                "children" => []
            ];

            // 3. Fetch Buildings under this Department
            $did = (int)$dept['id'];
            $res_buildings = $conn->query(
                "SELECT * FROM buildings WHERE department_id = $did ORDER BY name_en ASC"
            );

            if ($res_buildings) {
                while ($building = $res_buildings->fetch_assoc()) {
                    $b_img = $building['image_url'] ?? null;

                    $buildingNode = [
                        "id"                 => $building['id'],
                        "title_en"           => $building['name_en'],
                        "title_th"           => $building['name_th'],
                        "type"               => "building",
                        "lat"                => $building['latitude'],
                        "lng"                => $building['longitude'],
                        "image_url"          => makeFullUrl($b_img),
                        "responsible_email"  => $building['responsible_email'] ?? null,
                        "children"           => []
                    ];

                    // 4. Fetch Rooms under this Building
                    $bid = (int)$building['id'];
                    $res_rooms = $conn->query(
                        "SELECT * FROM rooms WHERE building_id = $bid ORDER BY room_number ASC"
                    );

                    if ($res_rooms) {
                        while ($room = $res_rooms->fetch_assoc()) {
                            $r_img    = $room['image_url'] ?? null;
                            $r_layout = $room['floor_layout_url'] ?? null;

                            $roomNode = [
                                "id"                 => $room['id'],
                                "title_en"           => $room['name_en'],
                                "title_th"           => $room['name_th'],
                                "room_number"        => $room['room_number'],
                                "floor"              => $room['floor'],
                                "details"            => $room['details'] ?? null,
                                "responsible_email"  => $building['responsible_email'] ?? null,
                                "type"               => "room",
                                "lat"                => $building['latitude'],
                                "lng"                => $building['longitude'],
                                "image_url"          => makeFullUrl($r_img),
                                "building_image_url" => makeFullUrl($b_img),
                                "floor_layout_url"   => makeFullUrl($r_layout)
                            ];
                            array_push($buildingNode['children'], $roomNode);
                        }
                    }

                    array_push($deptNode['children'], $buildingNode);
                }
            }

            array_push($facultyNode['children'], $deptNode);
        }
    }

    // Also fetch buildings with NO department_id under this faculty
    // (legacy / unassigned — omit so UI stays clean)

    array_push($result, $facultyNode);
}

echo json_encode($result);
?>