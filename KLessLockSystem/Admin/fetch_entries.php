<?php 
error_reporting(E_ALL);
include '../Includes/dbcon.php';

function fetchEntries($conn) {
    $query = "
        SELECT e.Entry_ID, e.User_ID, e.Record_ID, e.School_ID, e.Date_time
        FROM entry_table e
    ";
    
    if (!$result = mysqli_query($conn, $query)) {
        echo "Error: " . mysqli_error($conn);
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch entry records
$entryRecords = fetchEntries($conn);

$output = '';
foreach ($entryRecords as $row) {
    $output .= "<tr>
                    <td>{$row['Entry_ID']}</td>
                    <td>{$row['User_ID']}</td>
                    <td>{$row['Record_ID']}</td>
                    <td>" . htmlspecialchars($row['School_ID']) . "</td>
                    <td>" . date('Y-m-d H:i:s', strtotime($row['Date_time'])) . "</td>
                </tr>";
}

echo $output;
?>
