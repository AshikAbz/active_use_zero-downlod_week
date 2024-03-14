<?php
header("Content-Type: text/html; charset=utf-8");

$hostname = 'localhost';
$epesi_db = 'epesi';
$epesi_username = 'root';
$epesi_password = 'mysql';
$stats_db = 'stats';
$stats_username = 'root';
$stats_password = 'mysql';

try {
    
    $epesi_dbh = new PDO("mysql:host=$hostname; dbname=$epesi_db;", $epesi_username, $epesi_password);
    $epesi_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    //take yesterday date
    //$yesterday = date('Y-m-d', strtotime('-1 days')); its actual yeastrday date take.
    $yesterday = '2024-01-07'; //this is enable for hardcore
    if(isset($_SERVER['argv'][1]) && !empty($_SERVER['argv'][1]) && preg_match("/\d{4}\-\d{2}-\d{2}/", $_SERVER['argv'][1])) {
       $yesterday = $_SERVER['argv'][1];
    } elseif (isset($_GET['d']) && !empty($_GET['d']) && preg_match("/\d{4}\-\d{2}-\d{2}/", $_GET['d'])) {
        $yesterday = $_GET['d'];
    }
  
    $epesi_query = 'SELECT a.f_user_name AS userid, a.f_company_name FROM company_data_1 a LEFT JOIN DataBases_data_1 b ON a.id = b.f_text AND (:yd BETWEEN b.f_start_date AND b.f_expire_date) WHERE a.active = 1 AND b.active = 1 GROUP BY a.id'; //to take active user with db active between one week .
    $epesi_sth = $epesi_dbh->prepare($epesi_query);
    $epesi_sth->execute(array(':yd' => $yesterday));
    $rows = $epesi_sth->fetchAll(PDO::FETCH_ASSOC);
    $activeusers = array();
    if ($rows) {
        foreach ($rows as $row) {
            //var_dump($rows);
           $activeusers[strtolower($row['userid'])] = $row['f_company_name'];
        }
        
        try {
            $stats_dbh = new PDO("mysql:host=$hostname; dbname=$stats_db", $stats_username, $stats_password);
            $stats_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //back to seven days
            $startday = date('Ymd', strtotime('-6 days', strtotime($yesterday)));
            $endday = date('Ymd', strtotime($yesterday));
            
            $activeuserskey = array_keys($activeusers);
            $ids =  "'" . implode("','", $activeuserskey) . "'";
            $stats_query = "SELECT SUM(downloads) AS download, user_id FROM owa_outstanding WHERE user_id IN($ids) AND yyyymmdd BETWEEN :sd AND :ed GROUP BY user_id HAVING download = 0";//to  take active user passing from first quey that that zero downlods in one week.
            $stats_sth = $stats_dbh->prepare($stats_query);
            $stats_sth->execute(array(':sd' => $startday, ':ed' => $endday));
            $customers_count = $stats_sth->rowCount();
            if ($customers_count > 0) { // check  here customer available and then only print or entring to html,not customer here the html page not print
                $rows = $stats_sth->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <html>
                    <head>
                        <meta charset="utf-8">
                        <style>
                            table {
                                font-family: Helvetica Neue,Helvetica,Arial,sans-serif;
                                font-size: 14px;
                                width: 100%;
                                border-collapse: collapse;
                            }
                            th {
                                background-color: #003366;
                                color: #FFFFFF;
                                font-weight: bold;
                            }
                            th, td {
                                border: 0px solid #000;
                                padding: 5px;
                                text-align: left;
                            }
                            tr:nth-child(even) {
                                background-color: #e5ecf9; 
                            }
                            h2{
                                text-align:center;
                                font-weight:bold;
                            }
                        </style>
                    </head>
                    <body>
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                <td><?php echo $activeusers[strtolower($row['user_id'])]; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </body>
                </html>
            <?php
            }
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
    }
} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage();
}
?>   //og server upload file #active users zero download report for 1 week                                                                                                                            